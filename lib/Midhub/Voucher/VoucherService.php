<?php
namespace Midhub\Voucher;

use Midhub\Alert\AlertService;
use Midhub\Crypto;
use Midhub\Db;
use Midhub\Money;
use Midhub\Ops\CircuitBreaker;
use Midhub\Ops\Settings;
use Midhub\Wallet\InsufficientBalanceException;
use Midhub\Wallet\Wallet;

/**
 * Voucher storefront: catalog sync, purchase from the Main wallet (user.balance),
 * delivery into the encrypted voucher vault, refund on definite failure.
 */
final class VoucherService
{
    const SYNC_STATE = 'voucher_sync_state';
    const SYNC_LAST = 'voucher_sync_last';
    /** SQL condition: a product a customer may buy (never below cost, never a foreign-currency price). */
    const SELLABLE_SQL = "provider = 'popular_voucher' AND active = 1 AND in_stock = 1 AND sell_price > 0 AND sell_price >= cost_price AND (currency IS NULL OR currency = '' OR currency = 'BDT')";

    /**
     * Resumable catalog sync (the provider lists 14k+ products, 100 per page).
     * Runs pages until $budgetSeconds is used up, then stores the offset; the next call (admin
     * button or the cron worker) continues. New products start INACTIVE until the admin prices
     * them. Products with a markup % follow the provider cost. After a complete pass, products
     * the provider no longer lists are marked out of stock.
     * @return array{new:int,updated:int,skipped:int,delisted:int,fetched:int,total:?int,done:bool,mock:bool}
     */
    public static function syncCatalog(float $budgetSeconds = 20.0): array
    {
        $svc = PopularVoucherService::load();
        $cfg = $svc->config();
        $mock = $svc->isMock();
        $sig = $cfg->mode . '|' . $cfg->baseUrl;
        $st = json_decode(Settings::get(self::SYNC_STATE, ''), true);
        if (!is_array($st) || empty($st['pass']) || !isset($st['sig']) || $st['sig'] !== $sig || (isset($st['at']) && $st['at'] < time() - 86400)) {
            $st = array('sig' => $sig, 'offset' => 0, 'pass' => date('ymdHis') . bin2hex(random_bytes(3)), 'fetched' => 0, 'new' => 0, 'updated' => 0, 'skipped' => 0, 'total' => null);
        }
        $t0 = microtime(true);
        $done = false;
        do {
            $page = $svc->catalogPage((int) $st['offset']);
            list($new, $upd) = self::upsertProducts($page['items'], (string) $st['pass']);
            $st['new'] += $new;
            $st['updated'] += $upd;
            $st['skipped'] += $page['skipped'];
            $st['fetched'] += $page['received'];
            $st['total'] = $page['total'];
            $st['offset'] += PopularVoucherService::PAGE_SIZE;
            $done = $mock || $page['received'] < PopularVoucherService::PAGE_SIZE || ($page['total'] !== null && $st['offset'] >= $page['total']) || $st['offset'] >= 200000;
            $st['at'] = time();
        } while (!$done && microtime(true) - $t0 < $budgetSeconds);

        $delisted = 0;
        if ($done) {
            // Only after a complete live pass that saw (nearly) everything the provider reported.
            if (!$mock && $st['fetched'] > 0 && ($st['total'] === null || $st['fetched'] >= 0.9 * $st['total'])) {
                $delisted = Db::exec("UPDATE voucher_products SET in_stock = 0 WHERE provider = 'popular_voucher' AND in_stock = 1 AND (sync_pass IS NULL OR sync_pass <> ?)", 's', array($st['pass']));
            }
            Settings::set(self::SYNC_STATE, '', 'sync');
            Settings::set(self::SYNC_LAST, json_encode(array('at' => date('Y-m-d H:i:s'), 'new' => $st['new'], 'updated' => $st['updated'], 'skipped' => $st['skipped'], 'delisted' => $delisted, 'total' => $st['total'], 'mode' => $cfg->mode, 'mock' => $mock)), 'sync');
            $below = (int) Db::scalar("SELECT COUNT(*) FROM voucher_products WHERE provider = 'popular_voucher' AND active = 1 AND sell_price < cost_price");
            if ($below > 0) {
                AlertService::raise('voucher_below_cost', 'warning', 'Vouchers priced below cost',
                    "$below active voucher product(s) now cost more at the provider than their sell price. They are hidden from the store until repriced (Digital Vouchers, filter: below cost).", 720);
            }
        } else {
            Settings::set(self::SYNC_STATE, json_encode($st), 'sync');
        }
        return array('new' => $st['new'], 'updated' => $st['updated'], 'skipped' => $st['skipped'], 'delisted' => $delisted, 'fetched' => $st['fetched'], 'total' => $st['total'], 'done' => $done, 'mock' => $mock);
    }

    /** @return array{0:int,1:int} new, updated */
    private static function upsertProducts(array $items, string $pass): array
    {
        if (!$items) {
            return array(0, 0);
        }
        $codes = array_values(array_unique(array_map(function ($it) {
            return $it['code'];
        }, $items)));
        $existing = (int) Db::scalar("SELECT COUNT(*) FROM voucher_products WHERE provider = 'popular_voucher' AND product_code IN (" . implode(',', array_fill(0, count($codes), '?')) . ')',
            str_repeat('s', count($codes)), $codes);
        $rows = array();
        $types = '';
        $vals = array();
        foreach ($items as $it) {
            $rows[] = "('popular_voucher', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), ?)";
            $types .= 'sssssssssssisss' . 'ss';
            array_push($vals, $it['code'], $it['name'], $it['category'], $it['brand'], $it['country_code'], $it['country_name'], $it['face_value'],
                $it['price'], $it['price'], $it['currency'], $it['thumbnail'], $it['in_stock'] ? 1 : 0, $it['delivery_type'], $it['delivery_schema'],
                $it['id_label'], $it['id_pattern'], $pass);
        }
        Db::exec(
            "INSERT INTO voucher_products (provider, product_code, name, category, brand, country_code, country_name, face_value, cost_price, sell_price, currency, thumbnail_url, in_stock, delivery_type, delivery_schema, player_id_label, player_id_pattern, active, synced_at, sync_pass)
             VALUES " . implode(', ', $rows) . "
             ON DUPLICATE KEY UPDATE name = VALUES(name), category = VALUES(category), brand = VALUES(brand), country_code = VALUES(country_code), country_name = VALUES(country_name),
                face_value = VALUES(face_value), currency = VALUES(currency), thumbnail_url = VALUES(thumbnail_url), in_stock = VALUES(in_stock),
                delivery_type = VALUES(delivery_type), delivery_schema = VALUES(delivery_schema), player_id_label = VALUES(player_id_label), player_id_pattern = VALUES(player_id_pattern),
                sell_price = IF(markup_pct IS NULL, sell_price, CEILING(VALUES(cost_price) * (100 + markup_pct) / 100)),
                cost_price = VALUES(cost_price), synced_at = NOW(), sync_pass = VALUES(sync_pass)",
            $types, $vals
        );
        return array(count($codes) - $existing, $existing);
    }

    /** @return array<string,mixed>|null progress of an unfinished sync */
    public static function syncProgress(): ?array
    {
        $st = json_decode(Settings::get(self::SYNC_STATE, ''), true);
        return is_array($st) && !empty($st['offset']) ? $st : null;
    }

    /**
     * Sellable products for the storefront, filtered and paged (the catalog can hold thousands).
     * @param array{category?:string,q?:string} $f
     * @return array{items:array<int,array<string,mixed>>,total:int,categories:array<int,array{category:string,n:int}>}
     */
    public static function storefront(array $f = array(), int $limit = 500, int $offset = 0): array
    {
        $w = self::SELLABLE_SQL;
        $t = '';
        $p = array();
        $cat = isset($f['category']) ? trim((string) $f['category']) : '';
        if ($cat !== '') {
            $w .= ' AND category = ?';
            $t .= 's';
            $p[] = $cat;
        }
        $q = isset($f['q']) ? trim(substr((string) $f['q'], 0, 60)) : '';
        if ($q !== '') {
            $w .= ' AND (name LIKE ? OR brand LIKE ? OR country_name LIKE ?)';
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $t .= 'sss';
            array_push($p, $like, $like, $like);
        }
        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);
        return array(
            'items' => Db::all("SELECT id, name, category, brand, country_name, face_value, sell_price, thumbnail_url, delivery_type, delivery_schema, player_id_label, player_id_pattern
                                FROM voucher_products WHERE $w ORDER BY category, brand, sell_price LIMIT $limit OFFSET $offset", $t, $p),
            'total' => (int) Db::scalar("SELECT COUNT(*) FROM voucher_products WHERE $w", $t, $p),
            'categories' => Db::all('SELECT category, COUNT(*) AS n FROM voucher_products WHERE ' . self::SELLABLE_SQL . ' GROUP BY category ORDER BY n DESC'),
        );
    }
    /**
     * Validates the product and the customer's delivery value (Player ID / number) before any money moves.
     * @return array{ok:bool,message:string,price?:string,product?:array,player_id?:string}
     */
    public static function precheck(int $productId, string $playerId): array
    {
        $svc = PopularVoucherService::load();
        if (!$svc->config()->isActive()) {
            return array('ok' => false, 'message' => 'The voucher store is closed right now.');
        }
        if (!$svc->isMock() && $svc->checkEnvironment() !== '') {
            AlertService::raise('voucher_env_mismatch', 'critical', 'Voucher store blocked', $svc->checkEnvironment(), 60);
            return array('ok' => false, 'message' => 'The voucher store is temporarily unavailable.');
        }
        $p = Db::one('SELECT * FROM voucher_products WHERE id = ? AND ' . self::SELLABLE_SQL, 'i', array($productId));
        if (!$p) {
            return array('ok' => false, 'message' => 'This product is not available.');
        }
        $playerId = trim($playerId);
        if ($p['delivery_type'] === 'player_id') {
            $pattern = '/' . str_replace('/', '\/', (string) $p['player_id_pattern']) . '/';
            if (@preg_match($pattern, '') === false) {
                $pattern = '/^[0-9]{5,20}$/';
            }
            if ($playerId === '' || strlen($playerId) > 128 || !preg_match($pattern, $playerId)) {
                return array('ok' => false, 'message' => 'Enter a valid ' . $p['player_id_label'] . '.');
            }
        } else {
            $playerId = '';
        }
        return array('ok' => true, 'message' => '', 'price' => Money::fromPaisa(Money::toPaisa((string) $p['sell_price'])), 'product' => $p, 'player_id' => $playerId);
    }

    /**
     * Debits the Main wallet and places the order.
     * @return array{ok:bool,message:string,order_no?:string,status?:string}
     */
    public static function purchase(int $userId, int $productId, string $nonce, string $playerId = '', ?string $depositInvoice = null): array
    {
        // nonce: form nonce, or "inv:<invoice>" when the order is paid through a gateway (one order per payment)
        if (!preg_match('/^([a-f0-9]{32,64}|inv:MH\d{18})$/', $nonce)) {
            return array('ok' => false, 'message' => 'Your form expired. Please refresh and try again.');
        }
        $dup = Db::one('SELECT order_no, status FROM voucher_orders WHERE user_id = ? AND idempotency_key = ?', 'is', array($userId, $nonce));
        if ($dup) {
            return array('ok' => true, 'order_no' => $dup['order_no'], 'status' => $dup['status'], 'message' => 'This order was already placed (duplicate submit ignored).');
        }
        $chk = self::precheck($productId, $playerId);
        if (!$chk['ok']) {
            return array('ok' => false, 'message' => $chk['message']);
        }
        $svc = PopularVoucherService::load();
        $p = $chk['product'];
        $playerId = $chk['player_id'];
        $price = Money::fromPaisa(Money::toPaisa((string) $p['sell_price']));
        $orderNo = 'VO' . date('ymdHis') . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $mode = $svc->config()->mode;

        try {
            $orderId = Db::transaction(function () use ($userId, $p, $price, $orderNo, $mode, $nonce, $playerId, $depositInvoice) {
                $id = Db::insert(
                    "INSERT INTO voucher_orders (order_no, user_id, product_id, product_code, product_name, price, mode, status, idempotency_key, player_id, deposit_invoice)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'processing', ?, ?, ?)",
                    'siisssssss', array($orderNo, $userId, (int) $p['id'], $p['product_code'], trim($p['name'] . ' ' . $p['face_value']), $price, $mode, $nonce, $playerId !== '' ? $playerId : null, $depositInvoice)
                );
                Wallet::debit($userId, Wallet::MAIN, $price, 'voucher', $orderNo, 'Voucher ' . $p['name']);
                return $id;
            });
        } catch (InsufficientBalanceException $e) {
            return array('ok' => false, 'message' => 'Insufficient Main wallet balance. Price: ' . $price . ' Tk.');
        } catch (\RuntimeException $e) {
            if (stripos($e->getMessage(), 'Duplicate') !== false) {
                return array('ok' => false, 'message' => 'This order was already submitted.');
            }
            throw $e;
        }

        try {
            $r = $svc->placeOrder($p['product_code'], $orderNo, $playerId, (string) $p['delivery_schema']);
            if ($r['outcome'] === PopularVoucherService::DELIVERED) {
                CircuitBreaker::success('voucher');
            } elseif ($r['outcome'] === PopularVoucherService::PROCESSING && $r['provider_order_id'] === null) {
                CircuitBreaker::failure('voucher', 'voucher provider: ' . $r['message']);
            }
        } catch (\Throwable $e) {
            $r = array('outcome' => PopularVoucherService::PROCESSING, 'provider_order_id' => null, 'code' => null, 'serial' => null, 'pin' => null, 'expires' => null, 'message' => $e->getMessage());
        }
        $status = self::settle($orderId, $r);
        if ($status === 'delivered') {
            return array('ok' => true, 'order_no' => $orderNo, 'status' => $status, 'message' => $playerId !== '' ? 'Top-up successful: delivered to ' . $p['player_id_label'] . ' ' . $playerId . '.' : 'Purchase successful. Your code is in My Vouchers below.');
        }
        if ($status === 'refunded') {
            return array('ok' => false, 'order_no' => $orderNo, 'status' => $status, 'message' => 'The provider could not fulfil this order. ' . $price . ' Tk was refunded to your Main wallet.');
        }
        return array('ok' => true, 'order_no' => $orderNo, 'status' => $status, 'message' => 'Order placed and processing. The code will appear in My Vouchers shortly.');
    }

    /** Applies a provider outcome exactly once. Returns the resulting status. */
    public static function settle(int $orderId, array $r): string
    {
        return Db::transaction(function () use ($orderId, $r) {
            $o = Db::one('SELECT * FROM voucher_orders WHERE id = ? FOR UPDATE', 'i', array($orderId));
            if (!$o || $o['status'] !== 'processing') {
                return $o ? $o['status'] : 'failed';
            }
            if ($r['outcome'] === PopularVoucherService::DELIVERED) {
                Db::exec("UPDATE voucher_orders SET status = 'delivered', provider_order_id = ?, voucher_code = ?, voucher_serial = ?, voucher_pin = ?, expires_on = ?, delivered_at = NOW(), status_checked_at = NOW() WHERE id = ?",
                    'sssssi', array($r['provider_order_id'],
                        $r['code'] !== null ? Crypto::encrypt($r['code']) : null,
                        $r['serial'] !== null ? Crypto::encrypt($r['serial']) : null,
                        $r['pin'] !== null ? Crypto::encrypt($r['pin']) : null,
                        $r['expires'] !== null ? substr($r['expires'], 0, 30) : null, $orderId));
                Db::insert("INSERT INTO notification (UserId, submitted_time, message, MsgFrom, seen, date) VALUES (?, NOW(), ?, 'Voucher', '0', CURDATE())",
                    'is', array((int) $o['user_id'], substr('Your voucher ' . $o['product_name'] . ' is ready. Order ' . $o['order_no'] . '. Open Digital Vouchers to view the code.', 0, 250)));
                return 'delivered';
            }
            if ($r['outcome'] === PopularVoucherService::FAILED) {
                Db::exec("UPDATE voucher_orders SET status = 'refunded', provider_order_id = ?, failure_reason = ?, status_checked_at = NOW() WHERE id = ?",
                    'ssi', array($r['provider_order_id'], substr($r['message'] ?: 'provider rejected the order', 0, 255), $orderId));
                Wallet::credit((int) $o['user_id'], Wallet::MAIN, (string) $o['price'], 'voucher_refund', $o['order_no'], 'Refund: ' . $o['product_name']);
                return 'refunded';
            }
            Db::exec('UPDATE voucher_orders SET provider_order_id = COALESCE(?, provider_order_id), status_checked_at = NOW() WHERE id = ?',
                'si', array($r['provider_order_id'], $orderId));
            return 'processing';
        });
    }

    /** Cron: re-check orders still processing. Never auto-refunds an unknown outcome. */
    public static function poll(int $limit = 30): array
    {
        $svc = PopularVoucherService::load();
        $counts = array('delivered' => 0, 'refunded' => 0, 'processing' => 0);
        // Only orders placed in the CURRENT mode: a sandbox lookup must never settle a live order.
        $rows = Db::all("SELECT id, order_no, provider_order_id, player_id, created_at FROM voucher_orders WHERE status = 'processing' AND mode = ?
                         AND created_at < NOW() - INTERVAL 60 SECOND
                         AND (status_checked_at IS NULL OR status_checked_at < NOW() - INTERVAL 60 SECOND)
                         ORDER BY id LIMIT ?", 'si', array($svc->config()->mode, $limit));
        foreach ($rows as $o) {
            $r = $svc->orderStatus((string) $o['provider_order_id'], $o['order_no'], (string) $o['player_id'] !== '');
            $s = self::settle((int) $o['id'], $r);
            $counts[$s] = isset($counts[$s]) ? $counts[$s] + 1 : 1;
            if ($s === 'processing' && strtotime($o['created_at']) < time() - 6 * 3600) {
                AlertService::raise('voucher_stuck:' . $o['order_no'], 'warning', 'Voucher order stuck',
                    "Voucher order {$o['order_no']} has been processing for over 6 hours. Check it with the provider.", 1440);
            }
        }
        return $counts;
    }

    /** @return array<int,array<string,mixed>> the customer's orders with decrypted codes */
    public static function vault(int $userId, int $limit = 50): array
    {
        $rows = Db::all('SELECT * FROM voucher_orders WHERE user_id = ? ORDER BY id DESC LIMIT ?', 'ii', array($userId, $limit));
        foreach ($rows as &$r) {
            foreach (array('voucher_code', 'voucher_serial', 'voucher_pin') as $k) {
                try {
                    $r[$k] = $r[$k] !== null ? Crypto::decrypt($r[$k]) : null;
                } catch (\Throwable $e) {
                    $r[$k] = '[unavailable - contact support]';
                }
            }
        }
        unset($r);
        return $rows;
    }
}
