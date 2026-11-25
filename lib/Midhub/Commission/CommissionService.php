<?php
namespace Midhub\Commission;

use Midhub\Db;
use Midhub\Money;
use Midhub\Ops\Settings;
use Midhub\Wallet\Wallet;

/**
 * 5-level automatic commission on every SUCCESSFUL recharge (final concept, section 3.C).
 *
 * The recharge row already stores the requester and their upline chain at request time
 * (send_query.level1..level5, slot k = an account of level k-1). Each account in that chain
 * receives `rate[its level]` % of the recharge amount into its Recharge wallet.
 *
 * Exactly once: commission_payouts has UNIQUE(source, beneficiary), and payment happens only
 * while the recharge row is locked in status 1 (success). Called from every path that marks a
 * recharge successful: Paywell settle, admin "Success", the SIM modem bridge and the legacy
 * eFlexiload poller.
 *
 * Note: the legacy model also pays "upper commission" when balance is ADDED to a downline
 * (Commission Set page). If both are non-zero, uplines are paid twice for the same money;
 * the admin page warns about this.
 */
final class CommissionService
{
    public static function enabled(): bool
    {
        return Settings::get('recharge_commission_enabled', '0') === '1';
    }

    /** @return array<int,string> level => rate */
    public static function rates(): array
    {
        $out = array(0 => '0.00', 1 => '0.00', 2 => '0.00', 3 => '0.00', 4 => '0.00');
        foreach (Db::all('SELECT level, rate FROM recharge_commission_rates') as $r) {
            $out[(int) $r['level']] = (string) $r['rate'];
        }
        return $out;
    }

    /**
     * Pays the commission for a successful recharge. Safe to call repeatedly.
     * @return int number of payouts made by this call
     */
    public static function payForRecharge(int $sendQueryId): int
    {
        if (!self::enabled()) {
            return 0;
        }
        return Db::transaction(function () use ($sendQueryId) {
            $row = Db::one('SELECT id, user_id, phone, balance, status, operator, level1, level2, level3, level4, level5 FROM send_query WHERE id = ? FOR UPDATE', 'i', array($sendQueryId));
            if (!$row || (string) $row['status'] !== '1') {
                return 0;
            }
            $base = Money::fromPaisa(Money::toPaisa((string) $row['balance']));
            if (Money::toPaisa($base) <= 0) {
                return 0;
            }
            $rates = self::rates();
            $paid = 0;
            $seen = array();
            for ($slot = 1; $slot <= 5; $slot++) {
                $uid = (int) $row['level' . $slot];
                if ($uid <= 0 || isset($seen[$uid])) {
                    continue;
                }
                $seen[$uid] = true;
                $u = Db::one("SELECT id, level, active FROM user WHERE id = ?", 'i', array($uid));
                if (!$u || (int) $u['level'] > 4 || (string) $u['active'] !== '1') {
                    continue; // super admin and inactive accounts earn nothing
                }
                $level = (int) $u['level'];
                $rate = isset($rates[$level]) ? $rates[$level] : '0.00';
                $amount = Money::percent($base, $rate);
                if (Money::toPaisa($amount) <= 0) {
                    continue;
                }
                $inserted = Db::exec('INSERT IGNORE INTO commission_payouts (source_type, source_id, beneficiary_id, beneficiary_level, rate, base_amount, amount)
                                      VALUES (\'recharge\', ?, ?, ?, ?, ?, ?)', 'iiisss', array((int) $row['id'], $uid, $level, $rate, $base, $amount));
                if ($inserted !== 1) {
                    continue; // already paid
                }
                $bal = Wallet::credit($uid, Wallet::RECHARGE, $amount, 'commission', 'RC' . $row['id'], "Commission {$rate}% on recharge {$row['phone']}");
                $who = $uid === (int) $row['user_id'] ? 'your recharge' : 'a recharge by your network';
                Db::insert("INSERT INTO notification (UserId, submitted_time, message, MsgFrom, seen, date) VALUES (?, NOW(), ?, 'Commission', '0', CURDATE())",
                    'is', array($uid, substr("Commission $amount Tk ({$rate}%) for $who of $base Tk to {$row['phone']}. New Recharge Balance: {$bal['after']} Tk.", 0, 250)));
                $paid++;
            }
            return $paid;
        });
    }

    /** Admin save: rates per level + on/off. */
    public static function save(array $rates, bool $enabled, string $by): void
    {
        Db::transaction(function () use ($rates, $enabled, $by) {
            foreach ($rates as $level => $rate) {
                Db::exec('INSERT INTO recharge_commission_rates (level, rate) VALUES (?, ?) ON DUPLICATE KEY UPDATE rate = VALUES(rate)', 'is', array((int) $level, number_format((float) $rate, 2, '.', '')));
            }
            Settings::set('recharge_commission_enabled', $enabled ? '1' : '0', $by);
        });
    }
}
