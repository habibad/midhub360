<?php
namespace Midhub\Admin;

use Midhub\Commission\CommissionService;
use Midhub\Db;
use Midhub\Modem\ModemGateway;
use Midhub\Ops\CircuitBreaker;
use Midhub\Security\AdminAuth;
use Midhub\Security\Csrf;

/** Admin actions: recharge commission, circuit-breaker reset, SIM modem devices. */
final class OpsAdminController
{
    public static function saveCommission(): void
    {
        $admin = self::guardPin('recharge_commission.php');
        $rates = array();
        for ($l = 0; $l <= 4; $l++) {
            $v = isset($_POST['rate'][$l]) ? trim((string) $_POST['rate'][$l]) : '0';
            if (!is_numeric($v) || (float) $v < 0 || (float) $v > 10) {
                self::back('recharge_commission.php', 'error', 'Each rate must be between 0 and 10 percent.');
            }
            $rates[$l] = $v;
        }
        $sum = array_sum(array_map('floatval', $rates));
        if ($sum > 10) {
            self::back('recharge_commission.php', 'error', "Total commission across the chain would be $sum% of every recharge. Keep it within your Paywell margin (max 10%).");
        }
        CommissionService::save($rates, !empty($_POST['enabled']), (string) $admin['user']);
        self::back('recharge_commission.php', 'success', 'Recharge commission saved (' . (!empty($_POST['enabled']) ? 'ON' : 'OFF') . ', total up to ' . $sum . '% per recharge).');
    }

    public static function resetBreaker(): void
    {
        AdminAuth::requireSuperAdmin();
        Csrf::requireValidPost();
        $key = isset($_POST['cb_key']) ? (string) $_POST['cb_key'] : '';
        if (!preg_match('/^[a-z0-9_:.-]{1,80}$/', $key)) {
            self::back('admin_alerts.php', 'error', 'Invalid key.');
        }
        CircuitBreaker::reset($key);
        self::back('admin_alerts.php', 'success', "Service '$key' resumed.");
    }

    public static function createModemDevice(): void
    {
        $admin = self::guardPin('modem_devices.php');
        $name = trim((string) (isset($_POST['name']) ? $_POST['name'] : ''));
        if (!preg_match('/^[A-Za-z0-9 ._-]{2,80}$/', $name)) {
            self::back('modem_devices.php', 'error', 'Device name: 2-80 letters, numbers, spaces, dot, dash.');
        }
        list($id, $token) = ModemGateway::createDevice($name, (string) (isset($_POST['operators']) ? $_POST['operators'] : ''), (string) $admin['user']);
        $_SESSION['modem_new_token'] = array('id' => $id, 'token' => $token);
        self::back('modem_devices.php', 'success', "Device '$name' created. Copy its token now: it is shown only once.");
    }

    public static function toggleModemDevice(): void
    {
        self::guardPin('modem_devices.php');
        $id = (int) (isset($_POST['id']) ? $_POST['id'] : 0);
        Db::exec('UPDATE modem_devices SET active = 1 - active WHERE id = ?', 'i', array($id));
        self::back('modem_devices.php', 'success', 'Device updated.');
    }

    private static function guardPin(string $page): array
    {
        $admin = AdminAuth::requireSuperAdmin();
        Csrf::requireValidPost();
        $pin = AdminAuth::verifyPin((int) $admin['id'], trim((string) (isset($_POST['self_pin']) ? $_POST['self_pin'] : '')));
        if (!$pin['ok']) {
            self::back($page, 'error', $pin['error']);
        }
        return $admin;
    }

    private static function back(string $page, string $type, string $msg): void
    {
        $_SESSION['gw_flash'] = array('type' => $type, 'html' => htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'));
        header('Location: ' . $page);
        exit();
    }
}
