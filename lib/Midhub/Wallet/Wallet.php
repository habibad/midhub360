<?php
namespace Midhub\Wallet;

use Midhub\Db;
use Midhub\Money;

/**
 * Atomic wallet operations over the triple-wallet model:
 *   balance       = Main wallet
 *   balance2      = Recharge wallet
 *   bkash_balance = Banking & Utility wallet
 *
 * Every method MUST run inside Db::transaction(). The user row is locked with
 * SELECT ... FOR UPDATE, so concurrent requests on the same account serialize,
 * and each movement is written to wallet_ledger with before/after balances.
 */
final class Wallet
{
    const MAIN = 'balance';
    const RECHARGE = 'balance2';
    const BANKING = 'bkash_balance';

    /** @return array<string,string> column => customer-facing label */
    public static function labels(): array
    {
        return array(
            self::MAIN => 'Main Wallet',
            self::RECHARGE => 'Recharge Wallet',
            self::BANKING => 'Banking & Utility Wallet',
        );
    }

    public static function assertColumn(string $wallet): string
    {
        if (!array_key_exists($wallet, self::labels())) {
            throw new \InvalidArgumentException('Invalid wallet: ' . $wallet);
        }
        return $wallet;
    }

    /** Locks the user row and returns the current wallet balance. */
    public static function lock(int $userId, string $wallet): string
    {
        self::requireTx();
        $col = self::assertColumn($wallet);
        $row = Db::one("SELECT `$col` AS bal FROM user WHERE id = ? FOR UPDATE", 'i', array($userId));
        if (!$row) {
            throw new \RuntimeException('User not found: ' . $userId);
        }
        // toPaisa/fromPaisa (not parse) so a legacy negative balance is read, not rejected
        return Money::fromPaisa(Money::toPaisa((string) $row['bal']));
    }

    /**
     * @return array{before:string,after:string}
     * @throws InsufficientBalanceException
     */
    public static function debit(int $userId, string $wallet, string $amount, string $refType, string $refId, string $note = '', string $floor = '0.00'): array
    {
        $amount = Money::parse($amount);
        if (Money::toPaisa($amount) <= 0) {
            throw new \InvalidArgumentException('Debit amount must be positive');
        }
        $before = self::lock($userId, $wallet);
        $after = Money::sub($before, $amount);
        if (Money::cmp($after, $floor) < 0) {
            throw new InsufficientBalanceException($before, $amount);
        }
        $col = self::assertColumn($wallet);
        Db::exec("UPDATE user SET `$col` = ? WHERE id = ?", 'si', array($after, $userId));
        self::ledger($userId, $wallet, 'debit', $amount, $before, $after, $refType, $refId, $note);
        return array('before' => $before, 'after' => $after);
    }

    /** @return array{before:string,after:string} */
    public static function credit(int $userId, string $wallet, string $amount, string $refType, string $refId, string $note = ''): array
    {
        $amount = Money::parse($amount);
        if (Money::toPaisa($amount) <= 0) {
            throw new \InvalidArgumentException('Credit amount must be positive');
        }
        $before = self::lock($userId, $wallet);
        $after = Money::add($before, $amount);
        $col = self::assertColumn($wallet);
        Db::exec("UPDATE user SET `$col` = ? WHERE id = ?", 'si', array($after, $userId));
        self::ledger($userId, $wallet, 'credit', $amount, $before, $after, $refType, $refId, $note);
        return array('before' => $before, 'after' => $after);
    }

    private static function ledger(int $userId, string $wallet, string $dir, string $amount, string $before, string $after, string $refType, string $refId, string $note): void
    {
        Db::insert(
            'INSERT INTO wallet_ledger (user_id, wallet, direction, amount, balance_before, balance_after, ref_type, ref_id, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            'issssssss',
            array($userId, $wallet, $dir, $amount, $before, $after, substr($refType, 0, 30), substr($refId, 0, 60), substr($note, 0, 255))
        );
    }

    private static function requireTx(): void
    {
        if (!Db::inTransaction()) {
            throw new \LogicException('Wallet operations must run inside Db::transaction()');
        }
    }
}
