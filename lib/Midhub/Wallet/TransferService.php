<?php
namespace Midhub\Wallet;

use Midhub\Db;

/**
 * Upline -> direct-downline balance transfer, atomically.
 *
 * Replaces the legacy read-then-write transfer, which (a) accepted ANY user id as the
 * recipient and (b) could send the same balance twice under parallel requests, creating money.
 * Both rows are locked in id order (no deadlocks), debited/credited and ledgered in one
 * transaction.
 */
final class TransferService
{
    /**
     * @return array{from_after:string,to_before:string,to_after:string}
     * @throws InsufficientBalanceException|\DomainException
     */
    public static function toDownline(int $fromId, int $toId, string $wallet, string $amount, string $note): array
    {
        Wallet::assertColumn($wallet);
        if ($fromId <= 0 || $toId <= 0 || $fromId === $toId) {
            throw new \DomainException('Invalid recipient.');
        }
        return Db::transaction(function () use ($fromId, $toId, $wallet, $amount, $note) {
            // lock both accounts in a fixed order to avoid deadlocks between opposite transfers
            $first = min($fromId, $toId);
            $second = max($fromId, $toId);
            Db::one('SELECT id FROM user WHERE id = ? FOR UPDATE', 'i', array($first));
            Db::one('SELECT id FROM user WHERE id = ? FOR UPDATE', 'i', array($second));
            $to = Db::one('SELECT id, created_by, active FROM user WHERE id = ?', 'i', array($toId));
            if (!$to || (int) $to['created_by'] !== $fromId) {
                throw new \DomainException('You can only send balance to your own downline accounts.');
            }
            $ref = 'TR' . $fromId . '-' . $toId . '-' . time();
            $out = Wallet::debit($fromId, $wallet, $amount, 'transfer_out', $ref, $note);
            $in = Wallet::credit($toId, $wallet, $amount, 'transfer_in', $ref, $note);
            return array('from_after' => $out['after'], 'to_before' => $in['before'], 'to_after' => $in['after']);
        });
    }
}
