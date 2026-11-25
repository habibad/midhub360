<?php
namespace Midhub\Admin;

use Midhub\Crypto;
use Midhub\Db;
use Midhub\Gateway\GatewayConfig;
use Midhub\Gateway\GatewayFactory;
use Midhub\Gateway\GatewayRegistry;
use Midhub\Gateway\TokenCache;
use Midhub\Security\AdminAuth;
use Midhub\Security\Csrf;

/**
 * Admin > Gateway Settings: save + test connection.
 * Order of checks on save: super admin session -> CSRF token -> Self-PIN (with lockout)
 * -> field validation -> encrypted write -> audit log (field names only, never values).
 */
final class GatewayAdminController
{
    /** @return array<string,mixed> data for the settings page */
    public static function viewData(): array
    {
        $rows = array();
        foreach (Db::all('SELECT * FROM payment_gateways ORDER BY sort_order') as $r) {
            $rows[$r['gateway_slug']] = $r;
        }
        $out = array();
        foreach (GatewayRegistry::all() as $slug => $def) {
            if (!isset($rows[$slug])) {
                continue;
            }
            $r = $rows[$slug];
            $creds = array(
                'sandbox' => Crypto::decryptJson($r['sandbox_credentials']),
                'live' => Crypto::decryptJson($r['live_credentials']),
            );
            $masked = array();
            foreach (array('sandbox', 'live') as $m) {
                foreach ($def['fields'] as $k => $f) {
                    $v = isset($creds[$m][$k]) ? (string) $creds[$m][$k] : '';
                    $masked[$m][$k] = array(
                        'set' => $v !== '',
                        // non-secret values are shown; secrets only as a mask
                        'value' => empty($f['secret']) ? $v : '',
                        'mask' => !empty($f['secret']) ? Crypto::mask($v) : '',
                    );
                }
            }
            $extra = json_decode((string) $r['extra_config'], true) ?: array();
            $out[$slug] = array(
                'def' => $def,
                'row' => $r,
                'fields' => $masked,
                'extra' => $extra,
                'complete' => array(
                    'sandbox' => GatewayConfig::credentialsComplete($def, $creds['sandbox']),
                    'live' => GatewayConfig::credentialsComplete($def, $creds['live']),
                ),
                'return_url' => $def['return_url'] ? GatewayConfig::publicUrl(isset($def['return_path']) ? $def['return_path'] : 'gateway/return.php?gw=' . $slug) : null,
                'webhook_url' => $def['webhook_url'] ? GatewayConfig::publicUrl('gateway/webhook.php?gw=' . $slug) : null,
                'audit' => Db::all('SELECT admin_user, action, changes, ip, created_at FROM gateway_audit_log WHERE gateway_slug = ? ORDER BY id DESC LIMIT 5', 's', array($slug)),
            );
        }
        return $out;
    }

    public static function save(): void
    {
        $admin = AdminAuth::requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::back('', 'error', 'Invalid request method.');
        }
        Csrf::requireValidPost();
        $slug = isset($_POST['slug']) ? (string) $_POST['slug'] : '';
        if (!GatewayRegistry::exists($slug)) {
            self::back('', 'error', 'Unknown gateway.');
        }
        $pin = isset($_POST['self_pin']) ? trim((string) $_POST['self_pin']) : '';
        $pinCheck = AdminAuth::verifyPin((int) $admin['id'], $pin);
        if (!$pinCheck['ok']) {
            self::back($slug, 'error', $pinCheck['error']);
        }

        $def = GatewayRegistry::get($slug);
        $row = Db::one('SELECT * FROM payment_gateways WHERE gateway_slug = ?', 's', array($slug));
        if (!$row) {
            self::back($slug, 'error', 'Gateway row missing. Run database/migrate.php.');
        }

        $errors = array();
        $changed = array();
        $status = (isset($_POST['status']) && $_POST['status'] === 'active') ? 'active' : 'inactive';
        $mode = (isset($_POST['mode']) && $_POST['mode'] === 'live') ? 'live' : 'sandbox';

        // ---- base URLs ----
        $urls = array();
        foreach (array('sandbox', 'live') as $m) {
            $u = trim((string) (isset($_POST[$m . '_base_url']) ? $_POST[$m . '_base_url'] : ''));
            $u = rtrim($u, '/');
            if ($u !== '') {
                $scheme = strtolower((string) parse_url($u, PHP_URL_SCHEME));
                if (!filter_var($u, FILTER_VALIDATE_URL) || !in_array($scheme, array('http', 'https'), true)) {
                    $errors[] = ucfirst($m) . ' Base URL is not a valid URL.';
                } elseif ($m === 'live' && $scheme !== 'https') {
                    $errors[] = 'Live Base URL must use https://.';
                }
            }
            $urls[$m] = $u;
        }

        // ---- credentials (blank secret = keep existing) ----
        $creds = array(
            'sandbox' => Crypto::decryptJson($row['sandbox_credentials']),
            'live' => Crypto::decryptJson($row['live_credentials']),
        );
        foreach (array('sandbox', 'live') as $m) {
            $posted = isset($_POST['cred'][$m]) && is_array($_POST['cred'][$m]) ? $_POST['cred'][$m] : array();
            $clear = isset($_POST['clear'][$m]) && is_array($_POST['clear'][$m]) ? $_POST['clear'][$m] : array();
            foreach ($def['fields'] as $k => $f) {
                $old = isset($creds[$m][$k]) ? (string) $creds[$m][$k] : '';
                $new = isset($posted[$k]) ? trim((string) $posted[$k]) : '';
                if (!empty($f['secret'])) {
                    if (!empty($clear[$k])) {
                        $new = '';
                    } elseif ($new === '') {
                        $new = $old;
                    }
                }
                if (strlen($new) > 8000) {
                    $errors[] = "{$f['label']} ($m) is too long.";
                    continue;
                }
                if ($new !== '' && isset($f['prefix'][$m])) {
                    $prefixes = (array) $f['prefix'][$m];
                    $okPrefix = false;
                    foreach ($prefixes as $p) {
                        if (strpos($new, $p) === 0) {
                            $okPrefix = true;
                        }
                    }
                    if (!$okPrefix) {
                        $errors[] = "{$f['label']} for " . ucfirst($m) . ' must start with ' . implode(' or ', $prefixes) . '.';
                    }
                }
                if ($new !== $old) {
                    $changed[] = "$m.$k";
                }
                $creds[$m][$k] = $new;
            }
        }

        // ---- non-secret extra settings ----
        $extra = json_decode((string) $row['extra_config'], true) ?: array();
        $postedExtra = isset($_POST['extra']) && is_array($_POST['extra']) ? $_POST['extra'] : array();
        foreach ($def['extra'] as $k => $f) {
            $v = isset($postedExtra[$k]) ? trim((string) $postedExtra[$k]) : '';
            if ($k === 'operator_codes' && $v !== '') {
                $j = json_decode($v, true);
                if (!is_array($j)) {
                    $errors[] = 'Operator codes must be a JSON object, e.g. {"grameenphone":"GP"}.';
                }
            }
            if (substr($k, -5) === '_path' && $v !== '' && $v[0] !== '/') {
                $errors[] = "{$f['label']} must start with '/'.";
            }
            if ($k === 'low_balance_threshold' && $v !== '' && !is_numeric($v)) {
                $errors[] = 'Low-balance alert must be a number.';
            }
            if ((isset($extra[$k]) ? (string) $extra[$k] : '') !== $v) {
                $changed[] = "extra.$k";
            }
            $extra[$k] = substr($v, 0, 2000);
        }

        // ---- limits ----
        $charge = self::num('charge_percentage', 0, 20, $errors, 'Charge %');
        $min = self::num('min_amount', 0.01, 10000000, $errors, 'Min amount');
        $max = self::num('max_amount', 0.01, 10000000, $errors, 'Max amount');
        $rate = $slug === 'stripe' ? self::num('exchange_rate', 1, 1000, $errors, 'Exchange rate') : (float) $row['exchange_rate'];
        if ($min !== null && $max !== null && $max < $min) {
            $errors[] = 'Max amount must be greater than or equal to Min amount.';
        }

        // ---- activation guards: never let a half-configured gateway go active ----
        if ($status === 'active') {
            $mockVoucher = ($slug === 'popular_voucher' && $mode === 'sandbox' && $urls['sandbox'] === '');
            if (!$mockVoucher) {
                if ($urls[$mode] === '') {
                    $errors[] = 'Cannot activate: ' . ucfirst($mode) . ' Base URL is empty.';
                }
                if (!GatewayConfig::credentialsComplete($def, $creds[$mode])) {
                    $errors[] = 'Cannot activate: required ' . ucfirst($mode) . ' credentials are missing.';
                }
            }
        }

        if ($errors) {
            self::back($slug, 'error', implode('<br>', array_map(function ($e) {
                return htmlspecialchars($e, ENT_QUOTES, 'UTF-8');
            }, $errors)));
        }

        foreach (array('status' => $status, 'mode' => $mode) as $k => $v) {
            if ($row[$k] !== $v) {
                $changed[] = "$k:{$row[$k]}->$v";
            }
        }
        foreach (array('sandbox_base_url' => $urls['sandbox'], 'live_base_url' => $urls['live']) as $k => $v) {
            if ((string) $row[$k] !== $v) {
                $changed[] = $k;
            }
        }
        foreach (array('charge_percentage' => $charge, 'min_amount' => $min, 'max_amount' => $max, 'exchange_rate' => $rate) as $k => $v) {
            if ((float) $row[$k] !== (float) $v) {
                $changed[] = $k;
            }
        }

        Db::exec(
            'UPDATE payment_gateways SET status = ?, mode = ?, sandbox_base_url = ?, live_base_url = ?, sandbox_credentials = ?, live_credentials = ?,
                extra_config = ?, charge_percentage = ?, min_amount = ?, max_amount = ?, exchange_rate = ?, updated_by = ? WHERE gateway_slug = ?',
            'sssssssddddss',
            array($status, $mode, $urls['sandbox'], $urls['live'], Crypto::encryptJson($creds['sandbox']), Crypto::encryptJson($creds['live']),
                json_encode($extra, JSON_UNESCAPED_SLASHES), $charge, $min, $max, $rate, $admin['user'], $slug)
        );
        TokenCache::forget($slug); // never reuse a token minted with old credentials/mode
        GatewayConfig::clearCache();
        self::audit($slug, $admin, 'save', $changed);

        $label = $def['name'] . ' saved: ' . strtoupper($status) . ', ' . strtoupper($mode) . ' mode.';
        if ($mode === 'live' && $status === 'active') {
            $label .= ' LIVE money is now flowing through this gateway.';
        }
        self::back($slug, 'success', htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ($changed ? '' : ' (no changes)'));
    }

    public static function test(): void
    {
        $admin = AdminAuth::requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::back('', 'error', 'Invalid request method.');
        }
        Csrf::requireValidPost();
        $slug = isset($_POST['slug']) ? (string) $_POST['slug'] : '';
        if (!GatewayRegistry::exists($slug)) {
            self::back('', 'error', 'Unknown gateway.');
        }
        try {
            $cfg = GatewayConfig::load($slug, true);
            $r = GatewayFactory::any($slug, $cfg)->testConnection();
        } catch (\Throwable $e) {
            $r = array('ok' => false, 'message' => $e->getMessage());
        }
        self::audit($slug, $admin, 'test:' . ($r['ok'] ? 'ok' : 'fail'), array());
        self::back($slug, $r['ok'] ? 'success' : 'error', 'Test (' . htmlspecialchars(GatewayConfig::load($slug)->mode, ENT_QUOTES, 'UTF-8') . '): ' . htmlspecialchars($r['message'], ENT_QUOTES, 'UTF-8'));
    }

    // ------------------------------------------------------------------------

    private static function num(string $key, float $min, float $max, array &$errors, string $label): ?float
    {
        $v = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
        if ($v === '' || !is_numeric($v) || (float) $v < $min || (float) $v > $max) {
            $errors[] = "$label must be a number between $min and $max.";
            return null;
        }
        return (float) $v;
    }

    private static function audit(string $slug, array $admin, string $action, array $changed): void
    {
        Db::insert('INSERT INTO gateway_audit_log (gateway_slug, admin_id, admin_user, action, changes, ip) VALUES (?, ?, ?, ?, ?, ?)',
            'sissss', array($slug, (int) $admin['id'], (string) $admin['user'], $action, json_encode(array_values(array_unique($changed))),
                isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : 'cli'));
    }

    /** Flash + redirect back to the settings page (message is pre-escaped HTML). */
    private static function back(string $slug, string $type, string $html): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['gw_flash'] = array('type' => $type, 'html' => $html);
        header('Location: gateway_settings.php' . ($slug !== '' ? '?tab=' . rawurlencode($slug) : ''));
        exit();
    }
}
