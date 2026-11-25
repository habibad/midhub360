<?php
namespace Midhub\Admin;

use Midhub\Db;
use Midhub\Payment\DepositService;
use Midhub\Recharge\RechargeService;
use Midhub\Security\AdminAuth;
use Midhub\Security\Csrf;

/**
 * Admin re-check of a pending deposit or Paywell recharge against the provider.
 * Re-checking only ever applies the provider's authoritative status through the same
 * exactly-once settle() path, so it can never double-credit.
 */
final class DepositAdminController
{
    public static function recheck(): void
    {
        AdminAuth::requireSuperAdmin();
        Csrf::requireValidPost();
        $inv = isset($_POST['invoice_no']) ? (string) $_POST['invoice_no'] : '';
        if (!preg_match('/^MH\d{18}$/', $inv)) {
            self::back('gateway_transactions.php', 'error', 'Invalid invoice number.');
        }
        try {
            $r = DepositService::settle($inv, 'reconcile');
            self::back('gateway_transactions.php', $r['status'] === 'completed' ? 'success' : 'error', "$inv: {$r['status']} - {$r['message']}");
        } catch (\Throwable $e) {
            self::back('gateway_transactions.php', 'error', "$inv: check failed - " . $e->getMessage());
        }
    }

    public static function rechargeRecheck(): void
    {
        AdminAuth::requireSuperAdmin();
        Csrf::requireValidPost();
        $ref = isset($_POST['gateway_ref']) ? (string) $_POST['gateway_ref'] : '';
        if (!preg_match('/^RC\d{18}$/', $ref)) {
            self::back('gateway_transactions.php?view=recharge', 'error', 'Invalid recharge reference.');
        }
        try {
            $counts = RechargeService::poll(1, $ref);
            $row = Db::one('SELECT status, gateway_status FROM send_query WHERE gateway_ref = ?', 's', array($ref));
            $names = array('0' => 'pending', '1' => 'success', '3' => 'failed + refunded', '6' => 'still processing');
            $s = $row ? (isset($names[$row['status']]) ? $names[$row['status']] : $row['status']) : 'unknown';
            self::back('gateway_transactions.php?view=recharge', 'success', "$ref: $s" . ($row && $row['gateway_status'] ? " ({$row['gateway_status']})" : '') . (array_sum($counts) === 0 ? ' (checked less than 10s ago, or not a current-mode Paywell row)' : ''));
        } catch (\Throwable $e) {
            self::back('gateway_transactions.php?view=recharge', 'error', "$ref: check failed - " . $e->getMessage());
        }
    }

    private static function back(string $page, string $type, string $msg): void
    {
        $_SESSION['gw_flash'] = array('type' => $type, 'html' => htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'));
        header('Location: ' . $page);
        exit();
    }
}
