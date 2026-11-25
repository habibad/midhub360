<?php
namespace Midhub\Payment;

use Midhub\Alert\AlertService;
use Midhub\Db;
use Midhub\Gateway\GatewayConfig;
use Midhub\Gateway\GatewayException;
use Midhub\Gateway\GatewayFactory;
use Midhub\Gateway\PaymentResult;
use Midhub\Money;
use Midhub\Ops\CircuitBreaker;
use Midhub\Wallet\Wallet;

/**
 * Online add-money orchestration.
 *
 * create()  validates, writes an invoice (idempotent per form nonce) and opens the checkout.
 * settle()  locks the invoice row, asks the gateway for the authoritative status and
 *           credits the wallet exactly once, in one ACID transaction.
 *
 * A wallet is credited only when ALL of these hold:
 *   - the gateway confirmed the payment server-to-server (never from redirect params alone)
 *   - the echoed invoice number matches ours (when the provider returns one; mandatory for
 *     providers whose payment id arrives through the customer's browser)
 *   - the paid amount and currency equal what we asked for
 *   - the provider transaction id was not already used to settle another invoice
 * Anything else goes to status `review` with an admin alert instead of crediting.
 */
final class DepositService
{
    /** Providers that echo our invoice number back on verification (mandatory match). */
    const MUST_ECHO_INVOICE = array('bkash', 'nagad', 'uddoktapay', 'stripe');

    /**
     * @return array{invoice_no:string,redirect_url:string}
     * @throws \InvalidArgumentException|GatewayException
     */
    /**
     * BDT credited per USD: the admin exchange rate minus the safety margin (absorbs USD/BDT
     * swings). The Add Money page shows exactly this rate, so the preview matches the credit.
     */
    public static function creditRate(\Midhub\Gateway\GatewayConfig $cfg): string
    {
        $margin = (float) $cfg->extra('rate_margin_bdt');
        return number_format(max(1, (float) $cfg->exchangeRate - max(0, $margin)), 4, '.', '');
    }

    public static function create(int $userId, string $slug, string $wallet, string $amountInput, string $nonce, ?array $intent = null): array
    {
        Wallet::assertColumn($wallet);
        if (!preg_match('/^[a-f0-9]{32,64}$/', $nonce)) {
            throw new \InvalidArgumentException('Your form expired. Please refresh the page and try again.');
        }
        $cfg = GatewayConfig::load($slug, true);
        if (!$cfg->isActive() || $cfg->category !== 'deposit') {
            throw new \InvalidArgumentException('This payment method is not available right now.');
        }
        if (CircuitBreaker::isOpen('deposit:' . $slug)) {
            throw new \InvalidArgumentException('This payment method is temporarily unavailable. Please use another one or try again in a few minutes.');
        }

        // Idempotency: a double-click / refresh re-submitting the same form returns the same invoice.
        $existing = Db::one('SELECT * FROM gateway_transactions WHERE user_id = ? AND idempotency_key = ?', 'is', array($userId, $nonce));
        if ($existing) {
            if (in_array($existing['status'], array('initiated', 'pending'), true) && !empty($existing['checkout_url'])) {
                return array('invoice_no' => $existing['invoice_no'], 'redirect_url' => $existing['checkout_url']);
            }
            throw new \InvalidArgumentException('This payment request was already submitted. Please start a new one.');
        }

        $amount = Money::parse($amountInput);
        if (Money::toPaisa($amount) <= 0) {
            throw new \InvalidArgumentException('Enter a valid amount.');
        }
        $unit = $cfg->definition['currency'];
        if (Money::cmp($amount, Money::parse($cfg->minAmount)) < 0 || Money::cmp($amount, Money::parse($cfg->maxAmount)) > 0) {
            throw new \InvalidArgumentException("Amount must be between {$cfg->minAmount} and {$cfg->maxAmount} $unit.");
        }

        $charge = Money::percent($amount, $cfg->chargePercentage);
        $payable = Money::add($amount, $charge);
        $rate = null;
        if ($unit === 'USD') {
            if ((float) $cfg->exchangeRate <= 0) {
                throw new \InvalidArgumentException('Card payments are not available right now.');
            }
            $rate = self::creditRate($cfg);
            self::enforceNewCardCustomerLimit($userId, $cfg, $amount);
            $credit = Money::multiply($amount, $rate); // BDT credited, rate locked now
        } else {
            $credit = $amount;
        }

        $invoice = self::newInvoiceNo();
        $ip = isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : null;
        try {
            Db::insert(
                'INSERT INTO gateway_transactions (invoice_no, user_id, gateway_slug, mode, wallet, credit_amount, charge_amount, payable_amount, currency, exchange_rate, status, idempotency_key, client_ip, intent)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'initiated\', ?, ?, ?)',
                'sisssssssssss',
                array($invoice, $userId, $slug, $cfg->mode, $wallet, $credit, $charge, $payable, $unit, $rate, $nonce, $ip, $intent === null ? null : json_encode($intent))
            );
        } catch (\RuntimeException $e) {
            if (stripos($e->getMessage(), 'Duplicate') !== false) {
                throw new \InvalidArgumentException('This payment request was already submitted. Please refresh and try again.');
            }
            throw $e;
        }

        $txn = self::find($invoice);
        $customer = Db::one('SELECT id, user, fullname, phone, email FROM user WHERE id = ?', 'i', array($userId));
        try {
            $out = GatewayFactory::deposit($slug, $cfg)->createCheckout($txn, $customer ?: array('id' => $userId, 'user' => '', 'fullname' => '', 'phone' => '', 'email' => ''));
        } catch (\Throwable $e) {
            CircuitBreaker::failure('deposit:' . $slug, $e->getMessage());
            Db::exec("UPDATE gateway_transactions SET status = 'failed', failure_reason = ? WHERE invoice_no = ? AND status = 'initiated'", 'ss', array(substr($e->getMessage(), 0, 255), $invoice));
            if ($e instanceof GatewayException) {
                throw $e;
            }
            throw new GatewayException($e->getMessage(), 'Could not start the payment. Please try again later.', 0, $e);
        }
        CircuitBreaker::success('deposit:' . $slug);
        Db::exec(
            "UPDATE gateway_transactions SET status = 'pending', gateway_payment_id = ?, checkout_url = ?, gateway_state = ? WHERE invoice_no = ? AND status = 'initiated'",
            'ssss', array($out['gateway_payment_id'], $out['redirect_url'], json_encode($out['state']), $invoice)
        );
        return array('invoice_no' => $invoice, 'redirect_url' => $out['redirect_url']);
    }

    /**
     * Settles an invoice exactly once.
     *
     * @param string $source 'return' (customer redirect), 'webhook' or 'reconcile' (cron)
     * @param array<string,string> $params redirect parameters (for 'return')
     * @return array{status:string,message:string,txn:array}
     */
    public static function settle(string $invoiceNo, string $source, array $params = array()): array
    {
        return Db::transaction(function () use ($invoiceNo, $source, $params) {
            $txn = Db::one('SELECT * FROM gateway_transactions WHERE invoice_no = ? FOR UPDATE', 's', array($invoiceNo));
            if (!$txn) {
                throw new \InvalidArgumentException('Unknown invoice.');
            }
            if (!in_array($txn['status'], array('initiated', 'pending'), true)) {
                return array('status' => $txn['status'], 'message' => 'Already processed.', 'txn' => $txn);
            }
            $txn['state'] = json_decode((string) $txn['gateway_state'], true) ?: array();

            // Use the mode the invoice was created in, even if the admin switched since.
            $cfg = GatewayConfig::load($txn['gateway_slug'], true);
            if ($cfg->mode !== $txn['mode']) {
                return array('status' => 'pending', 'message' => 'Gateway mode changed since this invoice was created; it needs manual review.', 'txn' => $txn);
            }
            $gw = GatewayFactory::deposit($txn['gateway_slug'], $cfg);

            $result = ($source === 'return') ? $gw->completeReturn($txn, $params) : $gw->verify($txn);

            // Providers like UddoktaPay only reveal their payment id on return/webhook. Persist it
            // once the provider itself confirmed it belongs to OUR invoice, so the reconcile cron
            // can re-check a payment that is still pending.
            if (empty($txn['gateway_payment_id']) && $result->gatewayPaymentId && $result->invoiceNo !== null && hash_equals($txn['invoice_no'], $result->invoiceNo)) {
                Db::exec('UPDATE gateway_transactions SET gateway_payment_id = ? WHERE id = ? AND gateway_payment_id IS NULL', 'si', array($result->gatewayPaymentId, (int) $txn['id']));
                $txn['gateway_payment_id'] = $result->gatewayPaymentId;
            }

            if ($result->status === PaymentResult::PENDING) {
                return array('status' => 'pending', 'message' => 'Payment is not confirmed yet.', 'txn' => $txn);
            }
            if ($result->status === PaymentResult::FAILED || $result->status === PaymentResult::CANCELLED) {
                $newStatus = $result->status === PaymentResult::CANCELLED ? 'cancelled' : 'failed';
                Db::exec('UPDATE gateway_transactions SET status = ?, failure_reason = ? WHERE id = ?', 'ssi', array($newStatus, substr($result->message, 0, 255), (int) $txn['id']));
                $txn['status'] = $newStatus;
                return array('status' => $newStatus, 'message' => $result->message ?: 'Payment was not completed.', 'txn' => $txn);
            }

            // ---- COMPLETED at the gateway: validate before crediting ----
            $problem = self::validate($txn, $result);
            if ($problem !== null) {
                Db::exec("UPDATE gateway_transactions SET status = 'review', failure_reason = ? WHERE id = ?", 'si',
                    array(substr($problem . ' | provider trx ' . $result->gatewayTrxId, 0, 255), (int) $txn['id']));
                AlertService::raise('deposit_review:' . $txn['invoice_no'], 'critical', 'Deposit needs review',
                    "Invoice {$txn['invoice_no']} ({$txn['gateway_slug']}) was paid but NOT credited: $problem", 0);
                $txn['status'] = 'review';
                return array('status' => 'review', 'message' => 'Your payment was received and is being reviewed. Support will credit it shortly.', 'txn' => $txn);
            }

            $userId = (int) $txn['user_id'];
            $note = strtoupper($txn['gateway_slug']) . ' ' . $txn['invoice_no'] . ($txn['exchange_rate'] ? ' @ ' . $txn['exchange_rate'] . ' BDT/USD' : '');
            $bal = Wallet::credit($userId, $txn['wallet'], $txn['credit_amount'], 'deposit', $txn['invoice_no'], $note);

            Db::exec(
                "UPDATE gateway_transactions SET status = 'completed', gateway_trx_id = ?, completed_at = NOW(), failure_reason = NULL WHERE id = ?",
                'si', array($result->gatewayTrxId, (int) $txn['id'])
            );
            self::mirrorToLegacyReports($txn, $result, $bal['after']);
            $txn['status'] = 'completed';
            return array('status' => 'completed', 'message' => 'Payment successful. ' . $txn['credit_amount'] . ' BDT added to your ' . Wallet::labels()[$txn['wallet']] . '.', 'txn' => $txn);
        });
    }

    /** @return string|null problem description, null when the payment may be credited */
    private static function validate(array $txn, PaymentResult $r): ?string
    {
        if (in_array($txn['gateway_slug'], self::MUST_ECHO_INVOICE, true) && $r->invoiceNo === null) {
            return 'gateway did not echo our invoice number';
        }
        if ($r->invoiceNo !== null && !hash_equals($txn['invoice_no'], $r->invoiceNo)) {
            return "invoice mismatch (gateway says {$r->invoiceNo})";
        }
        if ($r->paidAmount === null) {
            return 'gateway did not report the paid amount';
        }
        if (Money::cmp(Money::fromPaisa(Money::toPaisa($r->paidAmount)), $txn['payable_amount']) !== 0) {
            return "amount mismatch (paid {$r->paidAmount}, expected {$txn['payable_amount']} {$txn['currency']})";
        }
        if ($r->currency !== null && strtoupper($r->currency) !== strtoupper($txn['currency'])) {
            return "currency mismatch ({$r->currency})";
        }
        if (!$r->gatewayTrxId) {
            return 'gateway did not return a transaction id';
        }
        $dup = Db::scalar(
            "SELECT invoice_no FROM gateway_transactions WHERE gateway_slug = ? AND gateway_trx_id = ? AND id <> ? AND status IN ('completed','review') LIMIT 1",
            'ssi', array($txn['gateway_slug'], $r->gatewayTrxId, (int) $txn['id'])
        );
        if ($dup) {
            return "transaction id {$r->gatewayTrxId} already used by invoice $dup";
        }
        return null;
    }

    /** Keeps the existing admin reports (add_fund history, notifications) complete. */
    private static function mirrorToLegacyReports(array $txn, PaymentResult $r, string $newBalance): void
    {
        $user = Db::one('SELECT user, level, created_by FROM user WHERE id = ?', 'i', array((int) $txn['user_id']));
        $types = array('balance' => 'Main', 'balance2' => 'Recharge', 'bkash_balance' => 'Mobile Banking');
        Db::insert(
            "INSERT INTO add_fund (user_id, username, level, boss_id, balance_type, category, mcategory, type, amount, account_name, account_number, transaction_id, sending_number, date, status, comment, document, invoice_no)
             VALUES (?, ?, ?, ?, ?, 'Online Gateway', ?, 'Online', ?, '0', 'Online', ?, 'Online', NOW(), '1', ?, '0', ?)",
            'isiississs',
            array((int) $txn['user_id'], $user ? $user['user'] : '', $user ? (int) $user['level'] : 0, $user ? (int) $user['created_by'] : 0,
                $types[$txn['wallet']], strtoupper($txn['gateway_slug']), (int) round((float) $txn['credit_amount']),
                substr((string) $r->gatewayTrxId, 0, 50), substr('Auto ' . $txn['mode'], 0, 50), $txn['invoice_no'])
        );
        Db::insert(
            "INSERT INTO notification (UserId, submitted_time, message, MsgFrom, seen, date) VALUES (?, NOW(), ?, 'Balance Added', '0', CURDATE())",
            'is',
            array((int) $txn['user_id'], substr('Online payment received via ' . strtoupper($txn['gateway_slug']) . '. Amount: ' . $txn['credit_amount'] . ' Tk added to your ' . Wallet::labels()[$txn['wallet']] . '. New Balance: ' . $newBalance . ' Tk. Invoice: ' . $txn['invoice_no'], 0, 250))
        );
    }

    /**
     * New international card customers: max $X per day until they have a completed card
     * payment older than N days (final concept: first 3 days, $30/day). Counts pending too.
     */
    private static function enforceNewCardCustomerLimit(int $userId, GatewayConfig $cfg, string $amountUsd): void
    {
        $days = (int) $cfg->extra('new_user_days');
        $limit = (string) $cfg->extra('new_user_daily_usd');
        if ($days <= 0 || !is_numeric($limit) || (float) $limit <= 0) {
            return;
        }
        $first = Db::scalar("SELECT MIN(completed_at) FROM gateway_transactions WHERE user_id = ? AND gateway_slug = ? AND status = 'completed'", 'is', array($userId, $cfg->slug));
        if ($first !== null && strtotime((string) $first) < time() - $days * 86400) {
            return; // established customer
        }
        $today = (string) Db::scalar("SELECT COALESCE(SUM(payable_amount - charge_amount), 0) FROM gateway_transactions WHERE user_id = ? AND gateway_slug = ? AND status IN ('initiated','pending','completed','review') AND created_at >= CURDATE()",
            'is', array($userId, $cfg->slug));
        $after = Money::add(Money::fromPaisa(Money::toPaisa($today)), $amountUsd);
        if (Money::cmp($after, Money::parse($limit)) > 0) {
            $left = Money::sub(Money::parse($limit), Money::fromPaisa(Money::toPaisa($today)));
            throw new \InvalidArgumentException("New card customers can add up to \$$limit per day for their first $days days. Remaining today: \$" . (Money::toPaisa($left) > 0 ? $left : '0.00') . '.');
        }
    }

    /**
     * Runs the follow-up action attached to a COMPLETED deposit (e.g. "buy this voucher").
     * Idempotent: the action itself is keyed by the invoice, and intent_done_at is set once.
     * @return array|null result of the action, null when there is none
     */
    public static function runIntent(string $invoiceNo)
    {
        $txn = self::find($invoiceNo);
        if (!$txn || $txn['status'] !== 'completed' || empty($txn['intent']) || !empty($txn['intent_done_at'])) {
            return null;
        }
        $intent = json_decode((string) $txn['intent'], true);
        if (!is_array($intent) || empty($intent['type'])) {
            return null;
        }
        $result = null;
        if ($intent['type'] === 'voucher') {
            $result = \Midhub\Voucher\VoucherService::purchase((int) $txn['user_id'], (int) $intent['product_id'], 'inv:' . $txn['invoice_no'],
                isset($intent['player_id']) ? (string) $intent['player_id'] : '', $txn['invoice_no']);
        }
        Db::exec('UPDATE gateway_transactions SET intent_done_at = NOW() WHERE id = ? AND intent_done_at IS NULL', 'i', array((int) $txn['id']));
        return $result;
    }

    /** @return array<string,mixed>|null */
    public static function find(string $invoiceNo)
    {
        return Db::one('SELECT * FROM gateway_transactions WHERE invoice_no = ?', 's', array($invoiceNo));
    }

    /** @return array<string,mixed>|null */
    public static function findByGatewayPaymentId(string $slug, string $paymentId)
    {
        return Db::one('SELECT * FROM gateway_transactions WHERE gateway_slug = ? AND gateway_payment_id = ? ORDER BY id DESC LIMIT 1', 'ss', array($slug, $paymentId));
    }

    /** "MH" + yymmddHHMMSS + 6 random digits = 20 chars, alphanumeric (fits every provider's limits). */
    public static function newInvoiceNo(): string
    {
        return 'MH' . date('ymdHis') . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
