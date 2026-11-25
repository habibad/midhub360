<?php
namespace Midhub\Recharge;

use Midhub\Alert\AlertService;
use Midhub\Commission\CommissionService;
use Midhub\Db;
use Midhub\Ops\CircuitBreaker;
use Midhub\Money;
use Midhub\Wallet\InsufficientBalanceException;
use Midhub\Wallet\Wallet;

/**
 * Mobile recharge disbursement from the Recharge wallet (user.balance2).
 *
 * submit():
 *   1. ONE transaction: lock the user row (FOR UPDATE), re-check balance and the
 *      low-balance floors, debit, insert send_query, write the ledger. Commit.
 *   2. Dispatch OUTSIDE the transaction (an HTTP call must not hold row locks):
 *        Paywell active      -> status 6 (processing) -> SUCCESS 1 | FAILED 3 + refund | PENDING stays 6
 *        legacy eFlexiload   -> unchanged legacy behaviour (6 on accept, else manual queue 0)
 *        no provider         -> status 0, admin manual queue (unchanged legacy behaviour)
 * settle():  applies a provider outcome exactly once (row lock + "status must still be 6").
 * poll():    cron/callback enquiry for rows stuck in 6. Never auto-refunds an unknown outcome.
 *
 * send_query.status codes (legacy): 0 pending/manual, 1 success, 3 cancelled+refunded,
 * 4 waiting, 6 sent to API / processing.
 */
final class RechargeService
{
    const OPERATORS = array('017' => 'grameenphone', '013' => 'grameenphone', '019' => 'banglalink', '014' => 'banglalink',
        '018' => 'robi', '016' => 'airtel', '015' => 'teletalk');

    /** send_recharge.php "type" radio values -> Paywell connection type */
    const LOAD_TYPES = array('1' => 'prepaid', '2' => 'postpaid', '3' => 'prepaid');

    public static function operatorFor(string $msisdn): ?string
    {
        $p = substr($msisdn, 0, 3);
        return isset(self::OPERATORS[$p]) ? self::OPERATORS[$p] : null;
    }

    /**
     * Upline columns exactly as the legacy engine stored them:
     * level{userLevel+1} = the requester, the higher levels = successive created_by bosses,
     * the lower levels = '0'.
     * @return array<int,string> keys 1..5
     */
    public static function uplineLevels(int $userId, int $userLevel): array
    {
        $levels = array(1 => '0', 2 => '0', 3 => '0', 4 => '0', 5 => '0');
        $current = $userId;
        for ($slot = $userLevel + 1; $slot <= 5; $slot++) {
            $levels[$slot] = (string) $current;
            $current = (int) Db::scalar('SELECT created_by FROM user WHERE id = ?', 'i', array($current));
        }
        return $levels;
    }

    /**
     * @param string $floor balance that must remain AFTER the debit (low-balance rules)
     * @return array{ok:bool,message:string,id?:int,status?:string}
     */
    public static function submit(int $userId, int $userLevel, string $msisdn, string $amountInput, string $loadType, string $floor, string $source = 'web'): array
    {
        if (!preg_match('/^01[3-9]\d{8}$/', $msisdn)) {
            return array('ok' => false, 'message' => 'Invalid mobile number.');
        }
        $operator = self::operatorFor($msisdn);
        if ($operator === null) {
            return array('ok' => false, 'message' => 'Unsupported operator prefix.');
        }
        if ($userLevel < 0 || $userLevel > 4) {
            return array('ok' => false, 'message' => 'Your account level cannot send recharge from this panel.');
        }
        try {
            $amount = Money::parse($amountInput);
        } catch (\InvalidArgumentException $e) {
            return array('ok' => false, 'message' => 'Invalid amount.');
        }
        // send_query.balance is INT; a fractional amount would be refunded differently than debited.
        if (Money::toPaisa($amount) % 100 !== 0) {
            return array('ok' => false, 'message' => 'Recharge amount must be a whole number of Taka.');
        }
        // Legacy form codes: 1 = Prepaid, 2 = Postpaid, 3 = Skitto/Others (GP sub-brand, own operator code)
        if (!isset(self::LOAD_TYPES[$loadType])) {
            return array('ok' => false, 'message' => 'Select Prepaid, Postpaid or Skitto.');
        }
        $conType = self::LOAD_TYPES[$loadType];
        $providerOperator = $loadType === '3' ? 'skitto' : $operator;

        $paywell = PaywellService::active();
        $provider = null;
        if ($paywell) {
            $cfg = $paywell->config();
            if (Money::cmp($amount, Money::parse($cfg->minAmount)) < 0 || Money::cmp($amount, Money::parse($cfg->maxAmount)) > 0) {
                return array('ok' => false, 'message' => "Amount must be between {$cfg->minAmount} and {$cfg->maxAmount} Tk.");
            }
            $provider = 'paywell';
            // Kill switch: while Paywell (or this operator at Paywell) is failing, route to the
            // manual queue instead of sending money into a broken provider.
            if (CircuitBreaker::isOpen('paywell') || CircuitBreaker::isOpen('paywell:' . $operator)) {
                $provider = null;
            }
        } elseif (class_exists('\\Api') && method_exists('\\Api', 'apistatus') && (int) \Api::apistatus('1', 'eFlexiload', 'Recharge') === 1) {
            $provider = 'eflexiload';
        }

        $ref = 'RC' . date('ymdHis') . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $levels = self::uplineLevels($userId, $userLevel);
        $ip = isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 200) : 'cli';
        $apiReid = $provider === 'eflexiload' ? mt_rand(100000, 999999) : 0;
        $initialStatus = $provider === null ? '0' : '6';
        $mode = $provider === 'paywell' ? $paywell->config()->mode : null;

        try {
            $id = Db::transaction(function () use ($userId, $amount, $floor, $ref, $msisdn, $loadType, $initialStatus, $operator, $levels, $ip, $source, $apiReid, $provider, $mode) {
                // Same-number guard, inside the lock so two parallel requests can't both pass it.
                $busy = (int) Db::scalar("SELECT COUNT(*) FROM send_query WHERE phone = ? AND status IN ('0','4') FOR UPDATE", 's', array($msisdn));
                if ($busy > 0) {
                    throw new \DomainException('You already request in this number. Please wait for completed before requested.');
                }
                $bal = Wallet::debit($userId, Wallet::RECHARGE, $amount, 'recharge', $ref, "Recharge $msisdn", $floor);
                return Db::insert(
                    "INSERT INTO send_query (user_id, phone, load_type, balance, submitted_date, status, s_time, operator, s_date,
                        level1, level2, level3, level4, level5, ip, rfrom, api_reid, txnid, msg, pre_balance, new_balance, gateway, gateway_mode, gateway_ref)
                     VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, '0', '0', ?, ?, ?, ?, ?)",
                    'isssssssssssssisssss',
                    array($userId, $msisdn, $loadType, (string) (int) round((float) $amount), $initialStatus, (string) time(), $operator,
                        $levels[1], $levels[2], $levels[3], $levels[4], $levels[5], $ip, $source, $apiReid,
                        $bal['before'], $bal['after'], $provider, $mode, $ref)
                );
            });
        } catch (InsufficientBalanceException $e) {
            return array('ok' => false, 'message' => Money::cmp($e->balance, $e->required) < 0
                ? 'Insufficient balance. Please contact with your boss!'
                : 'Cannot transaction with your limit balance');
        } catch (\DomainException $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }

        if ($provider === 'paywell') {
            $r = $paywell->topup($msisdn, $amount, $conType, $providerOperator, $ref);
            self::trackHealth($r, $operator);
            $final = self::settle($id, $r);
            if ($final === '1') {
                return array('ok' => true, 'id' => $id, 'status' => '1', 'message' => 'Recharge successful.');
            }
            if ($final === '3') {
                return array('ok' => false, 'id' => $id, 'status' => '3', 'message' => 'Recharge failed and the amount was refunded to your Recharge wallet. ' . self::shortReason($r));
            }
            return array('ok' => true, 'id' => $id, 'status' => '6', 'message' => 'Your Requested Transaction of service (<b>Recharge</b>) is processing. You will see the result in Recharge History shortly.');
        }
        if ($provider === 'eflexiload') {
            $status = \Api::apirequest($msisdn, (string) (int) round((float) $amount), $loadType, (string) $apiReid);
            if ($status !== 'SUCCESS') {
                // legacy behaviour: API refused -> manual queue
                Db::exec("UPDATE send_query SET status = '0', api_reid = 0, gateway = NULL WHERE id = ? AND status = '6'", 'i', array($id));
            }
        }
        return array('ok' => true, 'id' => $id, 'status' => $initialStatus, 'message' => 'Your Requested Transaction of service (<b>Recharge</b>) has been initiated.');
    }

    /**
     * Applies a Paywell outcome to a processing row exactly once.
     * @param array{outcome:string,status_code:string,message:string,trx_id:?string,raw:mixed} $r
     * @return string resulting send_query.status
     */
    public static function settle(int $id, array $r): string
    {
        return Db::transaction(function () use ($id, $r) {
            $row = Db::one('SELECT * FROM send_query WHERE id = ? FOR UPDATE', 'i', array($id));
            if (!$row) {
                throw new \RuntimeException("send_query $id not found");
            }
            if ((string) $row['status'] !== '6') {
                return (string) $row['status']; // already settled (by callback, poller or admin)
            }
            $resp = substr(json_encode($r['raw']), 0, 60000);
            $msg = substr(trim($r['status_code'] . ' ' . $r['message']), 0, 250);

            if ($r['outcome'] === PaywellService::SUCCESS) {
                Db::exec("UPDATE send_query SET status = '1', txnid = ?, msg = ?, gateway_status = 'success', gateway_response = ?, status_checked_at = NOW() WHERE id = ?",
                    'sssi', array(substr((string) ($r['trx_id'] ?: $row['gateway_ref']), 0, 150), $msg, $resp, $id));
                self::report($row, '1', (string) ($r['trx_id'] ?: $row['gateway_ref']), 'Paywell: ' . ($r['message'] ?: 'Success'));
                CommissionService::payForRecharge($id); // same transaction: success and commission commit together
                return '1';
            }
            if ($r['outcome'] === PaywellService::FAILED) {
                Db::exec("UPDATE send_query SET status = '3', msg = ?, gateway_status = 'failed', gateway_response = ?, status_checked_at = NOW() WHERE id = ?",
                    'ssi', array($msg, $resp, $id));
                Wallet::credit((int) $row['user_id'], Wallet::RECHARGE, Money::fromPaisa(Money::toPaisa((string) $row['balance'])), 'recharge_refund', (string) $row['gateway_ref'], 'Refund: ' . $msg);
                self::report($row, '3', 'N/A', 'Paywell failed, refunded: ' . ($r['message'] ?: $r['status_code']));
                return '3';
            }
            Db::exec('UPDATE send_query SET gateway_status = ?, gateway_response = ?, status_checked_at = NOW(), check_attempts = check_attempts + 1 WHERE id = ?',
                'ssi', array(substr('pending ' . $r['status_code'], 0, 40), $resp, $id));
            return '6';
        });
    }

    /**
     * Enquires Paywell for rows still processing. Called by cron and by the callback.
     * @return array<string,int> counts per resulting status
     */
    public static function poll(int $limit = 50, ?string $onlyRef = null): array
    {
        $svc = PaywellService::active();
        $counts = array('1' => 0, '3' => 0, '6' => 0);
        if (!$svc) {
            return $counts;
        }
        // Only rows sent in the CURRENT mode (a sandbox lookup of a live ref says "not found").
        $mode = $svc->config()->mode;
        if ($onlyRef !== null) {
            $rows = Db::all("SELECT id, gateway_ref, gateway_status, check_attempts FROM send_query WHERE gateway = 'paywell' AND gateway_mode = ? AND status = '6' AND gateway_ref = ?
                              AND (status_checked_at IS NULL OR status_checked_at < NOW() - INTERVAL 10 SECOND)", 'ss', array($mode, $onlyRef));
        } else {
            $rows = Db::all("SELECT id, gateway_ref, gateway_status, check_attempts FROM send_query WHERE gateway = 'paywell' AND gateway_mode = ? AND status = '6'
                              AND submitted_date < NOW() - INTERVAL 60 SECOND
                              AND (status_checked_at IS NULL OR status_checked_at < NOW() - INTERVAL 60 SECOND)
                              ORDER BY id ASC LIMIT ?", 'si', array($mode, $limit));
        }
        foreach ($rows as $row) {
            $r = $svc->enquiry((string) $row['gateway_ref']);
            // An enquiry "failure" can be a transient "not found yet". Refund only after the
            // provider has said not-successful on 3 CONSECUTIVE enquiries (>= ~1 min apart).
            // The streak lives in gateway_status ("failcheck:N") and resets on any other answer.
            if ($r['outcome'] === PaywellService::FAILED) {
                $streak = preg_match('/failcheck:(\d+)/', (string) $row['gateway_status'], $m) ? (int) $m[1] : 0;
                $streak++;
                if ($streak < 3) {
                    $r['outcome'] = PaywellService::PENDING;
                    $r['status_code'] = 'failcheck:' . $streak;
                }
            }
            $status = self::settle((int) $row['id'], $r);
            $counts[$status] = isset($counts[$status]) ? $counts[$status] + 1 : 1;
            if ($status === '6' && (int) $row['check_attempts'] + 1 >= 30) {
                AlertService::raise('recharge_stuck:' . $row['gateway_ref'], 'warning', 'Recharge stuck in processing',
                    "Recharge {$row['gateway_ref']} (send_query #{$row['id']}) has no final Paywell status after 30 checks. Verify it in the Paywell portal before settling it manually.", 1440);
            }
        }
        return $counts;
    }

    /**
     * Feeds the circuit breaker: provider-wide trouble (unreachable, 5xx, timeouts, token
     * failures) counts against "paywell"; structured rejections count against the operator.
     */
    private static function trackHealth(array $r, string $operator): void
    {
        $code = (string) $r['status_code'];
        if ($r['outcome'] === PaywellService::SUCCESS) {
            CircuitBreaker::success('paywell');
            CircuitBreaker::success('paywell:' . $operator);
        } elseif ($code === 'local' || strpos($code, 'curl_') === 0 || strpos($code, 'http_5') === 0) {
            CircuitBreaker::failure('paywell', 'Paywell ' . $code . ' ' . $r['message']);
        } elseif ($r['outcome'] === PaywellService::FAILED) {
            CircuitBreaker::failure('paywell:' . $operator, "Paywell rejected $operator recharge: $code " . $r['message']);
        }
    }

    private static function report(array $row, string $status, string $trx, string $msg): void
    {
        Db::insert(
            'INSERT INTO reportserver (company, msgbody, ucid, amt, transactionid, number, f_id, datetime, status, balance, date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, \'0\', CURDATE())',
            'ssssssss',
            array(substr((string) $row['operator'], 0, 50), substr($msg, 0, 1000), (string) $row['user_id'], (string) $row['balance'],
                substr($trx, 0, 50), (string) $row['phone'], (string) $row['id'], $status)
        );
    }

    private static function shortReason(array $r): string
    {
        return $r['message'] !== '' ? '(' . htmlspecialchars(substr($r['message'], 0, 120), ENT_QUOTES, 'UTF-8') . ')' : '';
    }
}
