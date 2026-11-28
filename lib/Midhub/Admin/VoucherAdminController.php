<?php
namespace Midhub\Admin;

use Midhub\Db;
use Midhub\Security\AdminAuth;
use Midhub\Security\Csrf;
use Midhub\Voucher\VoucherService;

/** Admin > Digital Vouchers: catalog sync, filtered browsing of 14k+ products, pricing and activation. */
final class VoucherAdminController
{
    const PER_PAGE = 50;

    public static function sync(): void
    {
        AdminAuth::requireSuperAdmin();
        Csrf::requireValidPost();
        @set_time_limit(60);
        try {
            $r = VoucherService::syncCatalog(20.0);
            $msg = $r['done']
                ? "Catalog synced: {$r['fetched']} product(s) read, {$r['new']} new (inactive until priced), {$r['updated']} updated, {$r['skipped']} skipped, {$r['delisted']} no longer listed (marked out of stock)."
                : "Sync in progress: {$r['fetched']} of " . ($r['total'] !== null ? $r['total'] : '?') . " read so far ({$r['new']} new, {$r['updated']} updated). It continues automatically every minute, or click Sync again.";
            self::back('success', $msg . ($r['mock'] ? ' [sandbox MOCK catalog]' : ''));
        } catch (\Throwable $e) {
            self::back('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /** Saves the price / activation of the products shown on one page (never touches other rows). */
    public static function saveProducts(): void
    {
        $admin = self::guard();
        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_slice(array_map('intval', $_POST['ids']), 0, 200) : array();
        $prices = isset($_POST['sell_price']) && is_array($_POST['sell_price']) ? $_POST['sell_price'] : array();
        $active = isset($_POST['active']) && is_array($_POST['active']) ? $_POST['active'] : array();
        $n = 0;
        $blocked = array();
        foreach ($ids as $id) {
            if ($id <= 0 || !isset($prices[$id])) {
                continue;
            }
            $price = trim((string) $prices[$id]);
            if ($price === '' || !is_numeric($price) || (float) $price < 0 || (float) $price > 1000000) {
                continue;
            }
            $p = Db::one('SELECT cost_price, sell_price, markup_pct, name FROM voucher_products WHERE id = ?', 'i', array($id));
            if (!$p) {
                continue;
            }
            $price = round((float) $price, 2);
            $on = !empty($active[$id]) ? 1 : 0;
            if ($on && ($price <= 0 || $price < (float) $p['cost_price'])) {
                $blocked[] = $p['name'];
                $on = 0; // never sell below provider cost
            }
            // a hand-edited price is fixed: it no longer follows the markup %
            $manual = abs($price - (float) $p['sell_price']) >= 0.005;
            $n += Db::exec('UPDATE voucher_products SET sell_price = ?, active = ?, markup_pct = IF(?, NULL, markup_pct) WHERE id = ?', 'diii', array($price, $on, $manual ? 1 : 0, $id));
        }
        self::back($blocked ? 'error' : 'success', "$n product(s) updated." . ($blocked ? ' Not activated (price below provider cost): ' . implode('; ', array_slice($blocked, 0, 10)) . (count($blocked) > 10 ? '; ...' : '') . '.' : ''));
    }

    /** Applies markup / activation to every product matching the current filter. */
    public static function bulk(): void
    {
        $admin = self::guard();
        list($where, $types, $params) = self::filterSql($_POST);
        $action = isset($_POST['bulk_action']) ? (string) $_POST['bulk_action'] : '';
        $matched = (int) Db::scalar("SELECT COUNT(*) FROM voucher_products WHERE $where", $types, $params);
        if ($action === 'markup') {
            $pct = isset($_POST['markup_pct']) ? trim((string) $_POST['markup_pct']) : '';
            if (!is_numeric($pct) || (float) $pct < 0 || (float) $pct > 500) {
                self::back('error', 'Markup must be between 0 and 500 %.');
            }
            $pct = round((float) $pct, 2);
            $n = Db::exec("UPDATE voucher_products SET markup_pct = ?, sell_price = CEILING(cost_price * (100 + ?) / 100) WHERE $where", 'dd' . $types, array_merge(array($pct, $pct), $params));
            self::back('success', "Markup {$pct}% applied to $matched product(s) ($n changed). Sell price = provider cost + {$pct}%, rounded up to whole Tk, and follows the cost on every sync.");
        }
        if ($action === 'activate') {
            $n = Db::exec("UPDATE voucher_products SET active = 1 WHERE $where AND sell_price > 0 AND sell_price >= cost_price", $types, $params);
            $below = (int) Db::scalar("SELECT COUNT(*) FROM voucher_products WHERE $where AND (sell_price <= 0 OR sell_price < cost_price)", $types, $params);
            self::back($below ? 'error' : 'success', "$n product(s) activated." . ($below ? " $below not activated: price below provider cost (apply a markup first)." : ''));
        }
        if ($action === 'deactivate') {
            $n = Db::exec("UPDATE voucher_products SET active = 0 WHERE $where", $types, $params);
            self::back('success', "$n product(s) deactivated.");
        }
        self::back('error', 'Choose a bulk action.');
    }

    /**
     * A voucher order stuck in "processing" (provider slow, reply lost, or completed without a code):
     *   check   - ask the provider again now (re-sends a lost order with the same Idempotency-Key)
     *   deliver - enter the code/PIN from the distributor portal; the customer sees it in My Vouchers
     *   refund  - only after confirming in the portal that the transaction failed: returns the
     *             price to the customer's Main wallet
     * Super Admin + Self-PIN, written to the gateway audit log.
     */
    public static function orderAction(): void
    {
        $admin = self::guard();
        $orderNo = isset($_POST['order_no']) ? (string) $_POST['order_no'] : '';
        $o = preg_match('/^VO\d{18}$/', $orderNo) ? Db::one('SELECT * FROM voucher_orders WHERE order_no = ?', 's', array($orderNo)) : null;
        if (!$o) {
            self::back('error', 'Order not found.');
        }
        if ($o['status'] !== 'processing') {
            self::back('error', "Order $orderNo is already {$o['status']}.");
        }
        $action = isset($_POST['order_action']) ? (string) $_POST['order_action'] : '';
        $base = array('provider_order_id' => $o['provider_order_id'], 'code' => null, 'serial' => null, 'pin' => null, 'expires' => null);
        if ($action === 'check') {
            $svc = \Midhub\Voucher\PopularVoucherService::load();
            if ($svc->config()->mode !== $o['mode']) {
                self::back('error', "Order $orderNo was placed in {$o['mode']} mode; switch the provider to that mode to check it.");
            }
            $status = VoucherService::check($o, $svc);
            self::auditOrder($admin, $orderNo, 'check:' . $status);
            self::back($status === 'processing' ? 'error' : 'success', "Order $orderNo: $status" . ($status === 'processing' ? ' (the provider has not finished it yet).' : '.'));
        }
        if ($action === 'deliver') {
            $code = isset($_POST['code']) ? trim((string) $_POST['code']) : '';
            $pin = isset($_POST['pin']) ? trim((string) $_POST['pin']) : '';
            if (($code === '' && $pin === '' && (string) $o['player_id'] === '') || strlen($code) > 200 || strlen($pin) > 100) {
                self::back('error', 'Enter the voucher code and/or PIN from the distributor portal.');
            }
            $status = VoucherService::settle((int) $o['id'], $base + array('outcome' => \Midhub\Voucher\PopularVoucherService::DELIVERED,
                'code' => $code !== '' ? $code : null, 'pin' => $pin !== '' ? $pin : null, 'message' => 'delivered manually by ' . $admin['user']));
            self::auditOrder($admin, $orderNo, 'deliver:' . $status);
            self::back('success', "Order $orderNo delivered; the customer sees it in My Vouchers.");
        }
        if ($action === 'refund') {
            $status = VoucherService::settle((int) $o['id'], $base + array('outcome' => \Midhub\Voucher\PopularVoucherService::FAILED,
                'message' => 'refunded by admin ' . $admin['user']));
            self::auditOrder($admin, $orderNo, 'refund:' . $status);
            self::back('success', "Order $orderNo refunded: {$o['price']} Tk returned to the customer's Main wallet.");
        }
        self::back('error', 'Choose an action.');
    }

    private static function auditOrder(array $admin, string $orderNo, string $action): void
    {
        Db::insert('INSERT INTO gateway_audit_log (gateway_slug, admin_id, admin_user, action, changes, ip) VALUES (?, ?, ?, ?, ?, ?)',
            'sissss', array('popular_voucher', (int) $admin['id'], (string) $admin['user'], substr('order ' . $action, 0, 50), json_encode(array($orderNo)),
                isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : 'cli'));
    }

    /**
     * WHERE clause for the admin filters (category, country, q, state, delivery).
     * @return array{0:string,1:string,2:array}
     */
    public static function filterSql(array $f): array
    {
        $w = array("provider = 'popular_voucher'");
        $t = '';
        $p = array();
        $get = function ($k) use ($f) {
            return isset($f[$k]) ? trim((string) $f[$k]) : '';
        };
        if ($get('category') !== '') {
            $w[] = 'category = ?';
            $t .= 's';
            $p[] = $get('category');
        }
        if ($get('country') !== '') {
            $w[] = 'country_code = ?';
            $t .= 's';
            $p[] = strtolower($get('country'));
        }
        if ($get('q') !== '') {
            $w[] = '(name LIKE ? OR brand LIKE ? OR product_code = ?)';
            $t .= 'sss';
            $like = '%' . addcslashes(substr($get('q'), 0, 60), '%_\\') . '%';
            array_push($p, $like, $like, $get('q'));
        }
        $states = array(
            'active' => 'active = 1', 'inactive' => 'active = 0', 'below_cost' => 'sell_price < cost_price',
            'out_of_stock' => 'in_stock = 0', 'markup' => 'markup_pct IS NOT NULL',
        );
        if (isset($states[$get('state')])) {
            $w[] = $states[$get('state')];
        }
        if (in_array($get('delivery'), array('code', 'player_id'), true)) {
            $w[] = 'delivery_type = ?';
            $t .= 's';
            $p[] = $get('delivery');
        }
        return array(implode(' AND ', $w), $t, $p);
    }

    /** @return array<string,string> the filter keys to carry through links and forms */
    public static function filterParams(array $f): array
    {
        $out = array();
        foreach (array('category', 'country', 'q', 'state', 'delivery') as $k) {
            if (isset($f[$k]) && trim((string) $f[$k]) !== '') {
                $out[$k] = substr(trim((string) $f[$k]), 0, 60);
            }
        }
        return $out;
    }

    private static function guard(): array
    {
        $admin = AdminAuth::requireSuperAdmin();
        Csrf::requireValidPost();
        $pin = AdminAuth::verifyPin((int) $admin['id'], isset($_POST['self_pin']) ? trim((string) $_POST['self_pin']) : '');
        if (!$pin['ok']) {
            self::back('error', $pin['error']);
        }
        return $admin;
    }

    private static function back(string $type, string $msg): void
    {
        $_SESSION['gw_flash'] = array('type' => $type, 'html' => htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'));
        $qs = http_build_query(self::filterParams($_POST) + (isset($_POST['page']) && (int) $_POST['page'] > 1 ? array('page' => (int) $_POST['page']) : array()));
        header('Location: voucher_products.php' . ($qs !== '' ? '?' . $qs : ''));
        exit();
    }
}
