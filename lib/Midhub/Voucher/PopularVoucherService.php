<?php
namespace Midhub\Voucher;

use Midhub\Gateway\GatewayConfig;
use Midhub\Gateway\GatewayException;
use Midhub\Http\HttpClient;
use Midhub\Http\HttpResponse;
use Midhub\Money;
use Midhub\Ops\Settings;

/**
 * Popular Vouchers "Catalogue & Distributor API" v1.1 connector
 * (base https://medback.popularvouchers.com, Authorization: Bearer <API key>).
 *
 * Confirmed against the live API (sandbox distributor account):
 *   GET /distributor/account                      -> {account:{id,name,status,balance,currency_code,environment}}
 *   GET /distributor/catalog?limit=100&offset=N   -> {products:[...], metadata:{skip,take,count}}
 *       product: variant_id, product_id, title, product_label (brand), variant_label (denomination),
 *                country_id/country_label, unit_amount (BDT), currency_code, thumbnail, channel
 *                (mobile_recharge | gift_card | bill_pay | data), delivery_schema (fields the
 *                customer must supply, e.g. DestNo phone/meter, User).
 *
 *   POST /distributor/transactions                (verified with sandbox orders on 2026-11-28)
 *       header Idempotency-Key: <our order no> (required; stored as external_id)
 *       body {variant_id, quantity: 1, delivery_details: {DestNo: "..."}}  (delivery only when the
 *       product has a delivery_schema; the provider normalises numbers, 017.. -> 88017..)
 *       -> 202 {transaction:{id:"dtx_...", status:"accepted", delivery_details, result:null,
 *               failure_code, failure_message, ...}, balance, replayed:false}
 *       same Idempotency-Key again -> 200, the SAME transaction, replayed:true (never a 2nd order)
 *       errors: 400 invalid_request / 400 "Delivery details do not match" {error:{code,message,fields}}
 *               404 product_not_found  (no transaction is created)
 *   GET  /distributor/transactions/{id}            -> {transaction:{status:"completed", result:{
 *               sandbox:true, delivery:{message, reference}}, ...}}; 404 not_found
 *
 * Money safety:
 *   - HTTP 4xx on order placement (except 408/409/425/429) = order definitely not created -> refund
 *   - timeout / 5xx / 409 / unreadable reply = unknown -> keep "processing"; the poller re-sends
 *     the SAME Idempotency-Key, which returns the existing transaction instead of a new one
 *   - a code is only accepted from a voucher field, never from an order-level "code"
 *   - live order "completed" without any voucher code -> stays processing + admin alert
 *     (the sandbox simulates delivery and returns no code; it gets a TEST placeholder)
 *
 * MOCK MODE: Sandbox with an empty Sandbox Base URL makes no network calls (demo catalog, TEST codes).
 */
final class PopularVoucherService
{
    const DELIVERED = 'delivered';
    const PROCESSING = 'processing';
    const FAILED = 'failed';
    const PAGE_SIZE = 100;

    /** @var GatewayConfig */
    private $cfg;

    public function __construct(GatewayConfig $cfg)
    {
        $this->cfg = $cfg;
    }

    public static function load(): self
    {
        return new self(GatewayConfig::load('popular_voucher', true));
    }

    public function config(): GatewayConfig
    {
        return $this->cfg;
    }

    public function isMock(): bool
    {
        return !$this->cfg->isLive() && $this->cfg->baseUrl === '';
    }

    // ------------------------------------------------------------------ catalog

    /**
     * One catalog page.
     * @return array{items:array<int,array<string,mixed>>,received:int,skipped:int,total:?int}
     */
    public function catalogPage(int $offset, int $limit = self::PAGE_SIZE): array
    {
        if ($this->isMock()) {
            $all = self::mockCatalog();
            return array('items' => $offset === 0 ? $all : array(), 'received' => $offset === 0 ? count($all) : 0, 'skipped' => 0, 'total' => count($all));
        }
        $base = $this->cfg->extra('catalog_path') ?: '/distributor/catalog';
        $path = $base . (strpos($base, '?') !== false ? '&' : '?') . 'limit=' . $limit . '&offset=' . $offset;
        // Page 0 is logged in full (to diagnose field changes); later pages log only the status (53 KB each).
        $j = $this->call('GET', $path, null, 'catalog', null, array('log_response' => $offset === 0, 'redact' => array()));
        $list = isset($j['products']) && is_array($j['products']) ? $j['products'] : (isset($j['data']) && is_array($j['data']) ? $j['data'] : array());
        $total = isset($j['metadata']['count']) ? (int) $j['metadata']['count'] : (isset($j['total']) ? (int) $j['total'] : null);
        $items = array();
        $skipped = 0;
        foreach ($list as $p) {
            $it = is_array($p) ? self::parseProduct($p) : null;
            if ($it === null) {
                $skipped++;
                continue;
            }
            $items[] = $it;
        }
        return array('items' => $items, 'received' => count($list), 'skipped' => $skipped, 'total' => $total);
    }

    /** Whole catalog (all pages). Prefer VoucherService::syncCatalog(), which is resumable. */
    public function catalog(int $maxPages = 500): array
    {
        $out = array();
        for ($page = 0, $offset = 0; $page < $maxPages; $page++, $offset += self::PAGE_SIZE) {
            $r = $this->catalogPage($offset);
            $out = array_merge($out, $r['items']);
            if ($r['received'] < self::PAGE_SIZE || ($r['total'] !== null && $offset + self::PAGE_SIZE >= $r['total'])) {
                break;
            }
        }
        return $out;
    }

    /**
     * Normalizes one provider product (v1.1 fields first, older spellings as fallback).
     * Returns null for products we cannot sell safely (no id/price, or more than one required
     * delivery field, which the storefront cannot collect yet).
     */
    public static function parseProduct(array $p): ?array
    {
        $code = self::pick($p, array('variant_id', 'product_code', 'code', 'sku', 'id'));
        $price = self::pick($p, array('unit_amount', 'price', 'cost', 'unit_price', 'rate'));
        if ($code === '' || $price === '' || !is_numeric($price) || (float) $price <= 0) {
            return null;
        }
        $fields = array();
        if (!empty($p['delivery_schema']) && is_array($p['delivery_schema'])) {
            foreach ($p['delivery_schema'] as $f) {
                if (!is_array($f) || !isset($f['name']) || !preg_match('/^[A-Za-z0-9_.-]{1,40}$/', (string) $f['name'])) {
                    continue;
                }
                $fields[] = array(
                    'name' => (string) $f['name'],
                    'type' => isset($f['type']) ? substr(strtolower((string) $f['type']), 0, 20) : 'text',
                    'label' => isset($f['label']) ? substr(trim((string) $f['label']), 0, 80) : (string) $f['name'],
                    'required' => !isset($f['required']) || (bool) $f['required'],
                    'max_length' => isset($f['max_length']) ? max(1, min(128, (int) $f['max_length'])) : 64,
                    'semantics' => isset($f['destination_semantics']) ? substr((string) $f['destination_semantics'], 0, 20) : '',
                );
            }
        }
        $required = array_values(array_filter($fields, function ($f) {
            return $f['required'];
        }));
        if (count($required) > 1) {
            return null;
        }
        $typeRaw = strtolower(self::pick($p, array('type', 'delivery_type', 'product_type')));
        $legacyDirect = in_array($typeRaw, array('topup', 'top-up', 'direct', 'player_id', 'uid'), true) || !empty($p['requires_player_id']);
        $f0 = $required ? $required[0] : null;
        $stockRaw = strtolower(self::pick($p, array('in_stock', 'stock', 'available', 'status')));
        $brand = self::pick($p, array('product_label', 'brand'));
        return array(
            'code' => substr($code, 0, 80),
            'name' => substr(self::pick($p, array('title', 'name', 'product_name')) ?: $code, 0, 150),
            'category' => substr(self::pick($p, array('channel', 'category', 'group')) ?: ($brand ?: 'Other'), 0, 60),
            'brand' => $brand !== '' ? substr($brand, 0, 120) : null,
            'country_code' => self::pick($p, array('country_id', 'country_code', 'country')) !== '' ? substr(strtolower(self::pick($p, array('country_id', 'country_code', 'country'))), 0, 8) : null,
            'country_name' => self::pick($p, array('country_label', 'country_name')) !== '' ? substr(self::pick($p, array('country_label', 'country_name')), 0, 80) : null,
            'face_value' => substr(self::pick($p, array('variant_label', 'face_value', 'denomination', 'value')), 0, 60),
            'price' => Money::fromPaisa(Money::toPaisa($price)),
            'currency' => self::pick($p, array('currency_code', 'currency')) !== '' ? substr(strtoupper(self::pick($p, array('currency_code', 'currency'))), 0, 8) : null,
            'thumbnail' => preg_match('#^https://[^\s"\'<>]{1,240}$#', self::pick($p, array('thumbnail', 'image', 'image_url'))) ? self::pick($p, array('thumbnail', 'image', 'image_url')) : null,
            'in_stock' => !in_array($stockRaw, array('0', 'false', 'out_of_stock', 'unavailable', 'inactive'), true),
            'delivery_type' => ($f0 !== null || $legacyDirect) ? 'player_id' : 'code',
            'delivery_schema' => $fields ? json_encode($fields, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
            'id_label' => $f0 !== null ? ($f0['label'] ?: $f0['name']) : 'Player ID',
            'id_pattern' => $f0 !== null ? self::patternFor($f0) : '^[0-9]{5,20}$',
        );
    }

    /** Validation regex for a delivery field (stored per product, checked before any debit). */
    public static function patternFor(array $f): string
    {
        $max = isset($f['max_length']) ? max(1, min(128, (int) $f['max_length'])) : 64;
        if ($f['type'] === 'tel' || $f['semantics'] === 'phone') {
            return '^\+?[0-9]{6,' . max(6, min(20, $max)) . '}$';
        }
        if ($f['type'] === 'number') {
            return '^[0-9]{1,' . $max . '}$';
        }
        if ($f['type'] === 'email') {
            return '^[^@\s]{1,64}@[^@\s]{1,120}\.[A-Za-z]{2,}$';
        }
        if ($f['semantics'] === 'meter') {
            return '^[A-Za-z0-9-]{3,' . max(3, $max) . '}$';
        }
        return '^[^<>\x00-\x1F]{1,' . $max . '}$';
    }

    // ------------------------------------------------------------------ orders

    /**
     * @param string $deliverySchema the product's stored delivery_schema JSON ('' for code products)
     * @return array{outcome:string,provider_order_id:?string,code:?string,serial:?string,pin:?string,expires:?string,message:string}
     */
    public function placeOrder(string $productCode, string $orderNo, string $playerId = '', string $deliverySchema = ''): array
    {
        if ($this->isMock()) {
            if ($playerId !== '') {
                // mock direct top-up: "000000" (or any non-numeric id) is rejected like a wrong player id
                $bad = !ctype_digit($playerId) || trim($playerId, '0') === '';
                return self::result($bad ? self::FAILED : self::DELIVERED, 'MOCK-' . $orderNo, null, null, null, null, $bad ? 'Invalid player ID' : 'Sandbox mock top-up delivered to player ' . $playerId);
            }
            return self::result(self::DELIVERED, 'MOCK-' . $orderNo,
                'TEST-' . strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4))),
                'SN' . random_int(100000000, 999999999), (string) random_int(10000000, 99999999), date('Y-m-d', strtotime('+1 year')),
                'Sandbox mock delivery (not a real voucher)');
        }
        $env = $this->checkEnvironment();
        if ($env !== '') {
            return self::result(self::FAILED, null, null, null, null, null, $env); // nothing was sent
        }
        $body = array(
            ($this->cfg->extra('order_product_field') ?: 'variant_id') => $productCode,
            'quantity' => 1,
        );
        $refField = $this->cfg->extra('order_reference_field');
        if ($refField !== '' && $refField !== '-') {
            $body[$refField] = $orderNo; // optional: the Idempotency-Key header already carries it
        }
        $merchant = $this->cfg->cred('merchant_id', false);
        if ($merchant !== '') {
            $body['merchant_id'] = $merchant;
        }
        if ($playerId !== '') {
            $schema = json_decode($deliverySchema, true);
            $field = is_array($schema) && isset($schema[0]['name']) ? (string) $schema[0]['name'] : '';
            foreach (is_array($schema) ? $schema : array() as $f) {
                if (!empty($f['required'])) {
                    $field = (string) $f['name'];
                    break;
                }
            }
            if ($field === '') {
                $body[$this->cfg->extra('player_id_param') ?: 'player_id'] = $playerId; // legacy direct top-up products
            } else {
                $container = $this->cfg->extra('order_delivery_field');
                if ($container === '-') {
                    $body[$field] = $playerId; // admin chose "top level"
                } else {
                    $body[$container ?: 'delivery_details'] = array($field => $playerId);
                }
            }
        }
        $resp = $this->request('POST', $this->cfg->extra('order_path') ?: '/distributor/transactions', $body, 'place_order', $orderNo, array('Idempotency-Key' => $orderNo));
        $j = $resp->json();
        if ($resp->errno !== 0 || $resp->status >= 500 || $resp->status === 0 || in_array($resp->status, array(408, 409, 425, 429), true)) {
            // unknown: the order may exist at the provider
            return self::result(self::PROCESSING, null, null, null, null, null, 'provider: HTTP ' . $resp->status . ' ' . $resp->error . ' ' . self::message(is_array($j) ? $j : array()));
        }
        if ($resp->status >= 400) {
            // the provider refused the request: no order was created
            return self::result(self::FAILED, null, null, null, null, null, self::message(is_array($j) ? $j : array()) ?: 'provider rejected the order (HTTP ' . $resp->status . ')');
        }
        if (!is_array($j)) {
            return self::result(self::PROCESSING, null, null, null, null, null, 'provider: unreadable reply');
        }
        return $this->parseOrder($j, $playerId !== '');
    }

    public function orderStatus(string $providerOrderId, string $orderNo, bool $direct = false): array
    {
        if ($this->isMock()) {
            // Mock orders are delivered instantly; never settle anything from mock mode.
            return self::result(self::PROCESSING, $providerOrderId, null, null, null, null, 'mock mode: no status lookup');
        }
        $id = $providerOrderId !== '' ? $providerOrderId : $orderNo;
        $resp = $this->request('GET', str_replace('{order_id}', rawurlencode($id), $this->cfg->extra('order_status_path') ?: '/distributor/transactions/{order_id}'), null, 'order_status', $orderNo);
        $j = $resp->json();
        if ($resp->status < 200 || $resp->status >= 300 || !is_array($j)) {
            // a failed lookup (even 404) is never proof that the order failed
            return self::result(self::PROCESSING, $providerOrderId !== '' ? $providerOrderId : null, null, null, null, null, 'status lookup: HTTP ' . $resp->status);
        }
        $r = $this->parseOrder($j, $direct);
        if ($r['provider_order_id'] === null && $providerOrderId !== '') {
            $r['provider_order_id'] = $providerOrderId;
        }
        return $r;
    }

    // ------------------------------------------------------------------ account

    /** @return array<string,mixed>|null the distributor account; also records its environment */
    public function account(): ?array
    {
        if ($this->isMock()) {
            return null;
        }
        $j = $this->call('GET', $this->cfg->extra('balance_path') ?: '/distributor/account', null, 'account', null);
        $a = isset($j['account']) && is_array($j['account']) ? $j['account'] : (isset($j['data']) && is_array($j['data']) ? $j['data'] : $j);
        if (isset($a['environment']) && is_string($a['environment'])) {
            Settings::set('voucher_provider_env:' . $this->cfg->mode, strtolower(substr($a['environment'], 0, 20)), 'system');
        }
        return $a;
    }

    public function balance(): ?string
    {
        $a = $this->account();
        $b = is_array($a) && isset($a['balance']) ? $a['balance'] : null;
        return is_numeric($b) ? Money::fromPaisa(Money::toPaisa((string) $b)) : null;
    }

    /**
     * '' when the configured mode matches the provider account's environment, else the reason
     * orders are blocked. A LIVE store with a SANDBOX key would charge customers real money for
     * test vouchers, so that combination never places orders.
     */
    public function checkEnvironment(): string
    {
        $env = Settings::get('voucher_provider_env:' . $this->cfg->mode, '');
        if ($this->cfg->isLive() && in_array($env, array('sandbox', 'test', 'testing'), true)) {
            return 'The voucher store is in LIVE mode but the API key belongs to the provider SANDBOX. Orders are blocked until a live key is saved.';
        }
        return '';
    }

    /** HMAC-SHA256 of the raw body with api_secret (or api_key) in X-Signature. */
    public function verifyWebhook(string $rawBody, array $headers): bool
    {
        $secret = $this->cfg->cred('api_secret', false) ?: $this->cfg->cred('api_key');
        $sig = isset($headers['x-signature']) ? strtolower(trim((string) $headers['x-signature'])) : '';
        return $sig !== '' && hash_equals(hash_hmac('sha256', $rawBody, $secret), $sig);
    }

    public function testConnection(): array
    {
        if ($this->isMock()) {
            return array('ok' => true, 'message' => 'Sandbox MOCK mode (no Sandbox Base URL): demo catalog and TEST codes, no network calls.');
        }
        try {
            $info = '';
            $warn = '';
            try {
                $a = $this->account();
                if (is_array($a) && $a) {
                    $env = isset($a['environment']) ? strtolower((string) $a['environment']) : '';
                    $info = 'Account: ' . (isset($a['name']) ? $a['name'] : 'Distributor') . ' (' . (isset($a['currency_code']) ? strtoupper((string) $a['currency_code']) : 'BDT') . ' '
                        . (isset($a['balance']) ? $a['balance'] : '?') . ', provider environment: ' . ($env ?: 'unknown') . ', status: ' . (isset($a['status']) ? $a['status'] : '?') . '). ';
                    $sandboxKey = in_array($env, array('sandbox', 'test', 'testing'), true);
                    if ($this->cfg->isLive() && $sandboxKey) {
                        $warn = 'BLOCKED: LIVE mode with a SANDBOX key - customers would pay for test vouchers. Save the live key. ';
                    } elseif (!$this->cfg->isLive() && $env !== '' && !$sandboxKey) {
                        $warn = 'WARNING: Sandbox mode with a LIVE provider key - test orders spend real distributor balance. ';
                    }
                }
            } catch (\Throwable $e) {
                $info = 'Account check failed (' . $e->getMessage() . '). ';
            }
            $page = $this->catalogPage(0, 1);
            $total = $page['total'] !== null ? $page['total'] : $page['received'];
            return array(
                'ok' => $warn === '' || strpos($warn, 'WARNING') === 0,
                'message' => $warn . $info . 'Catalog reachable: ' . $total . ' product(s) (' . $this->cfg->mode . ').',
            );
        } catch (GatewayException $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ parsing

    private function parseOrder(array $j, bool $direct = false): array
    {
        $root = $j;
        foreach (array('transaction', 'order', 'data') as $k) {
            if (isset($root[$k]) && is_array($root[$k]) && !isset($root[$k][0])) {
                $root = $root[$k];
                break;
            }
        }
        // v1.1 transaction: "id" is the dtx_ id used for status lookups ("order_id" is null)
        $orderId = self::pick($root, array('id', 'order_id', 'transaction_id', 'reference_id'));
        $status = strtolower(self::pick($root, array('status', 'order_status', 'state', 'fulfillment_status')));
        $result = isset($root['result']) && is_array($root['result']) ? $root['result'] : array();
        $delivery = isset($result['delivery']) && is_array($result['delivery']) ? $result['delivery'] : array();

        // Voucher codes live in a voucher list: vouchers / codes / items[...].vouchers / fulfillments,
        // a delivery_details LIST (for a phone-number product it is the {DestNo} object instead),
        // or inside result / result.delivery.
        $v = array();
        $lists = array();
        foreach (array('vouchers', 'codes', 'items', 'fulfillments', 'line_items', 'delivery_details') as $k) {
            $lists[] = isset($root[$k]) ? $root[$k] : null;
        }
        foreach (array('vouchers', 'codes', 'items', 'delivery_details') as $k) {
            $lists[] = isset($result[$k]) ? $result[$k] : null;
            $lists[] = isset($delivery[$k]) ? $delivery[$k] : null;
        }
        foreach ($lists as $list) {
            if (!empty($list) && is_array($list) && isset($list[0]) && is_array($list[0])) {
                $v = $list[0];
                foreach (array('vouchers', 'codes') as $k2) {
                    if (!empty($v[$k2]) && is_array($v[$k2]) && isset($v[$k2][0]) && is_array($v[$k2][0])) {
                        $v = array_merge($v, $v[$k2][0]);
                    }
                }
                break;
            }
        }
        $explicit = array('voucher_code', 'redeem_code', 'redemption_code', 'claim_code', 'card_number');
        $code = self::pick($v, array_merge(array('voucher_code', 'code'), $explicit));
        if ($code === '') {
            // never an order-level or delivery-level plain "code" (could be a status code)
            $code = self::pick($root, $explicit) ?: self::pick($delivery, $explicit);
        }
        $pin = self::pick($v, array('pin', 'voucher_pin', 'card_pin')) ?: (self::pick($root, array('voucher_pin', 'card_pin', 'pin')) ?: self::pick($delivery, array('voucher_pin', 'card_pin', 'pin')));
        $message = self::pick($root, array('failure_message')) ?: (self::pick($delivery, array('message')) ?: self::message($j));
        $out = self::result(self::PROCESSING, $orderId !== '' ? substr($orderId, 0, 120) : null, $code !== '' ? $code : null,
            (self::pick($v, array('serial', 'serial_number', 'voucher_serial')) ?: (self::pick($root, array('serial', 'serial_number', 'voucher_serial')) ?: self::pick($delivery, array('serial', 'serial_number', 'voucher_serial')))) ?: null,
            $pin !== '' ? $pin : null,
            (self::pick($v, array('expiry', 'expires_at', 'expiry_date', 'valid_till')) ?: (self::pick($root, array('expiry', 'expires_at', 'expiry_date', 'valid_till')) ?: self::pick($delivery, array('expiry', 'expires_at', 'expiry_date', 'valid_till')))) ?: null,
            substr($message, 0, 240));
        $failedWords = array('failed', 'failure', 'cancelled', 'canceled', 'rejected', 'refunded', 'declined', 'out_of_stock', 'error', 'expired');
        $doneWords = array('completed', 'complete', 'success', 'successful', 'succeeded', 'delivered', 'fulfilled');
        $openWords = array('accepted', 'pending', 'processing', 'queued', 'in_progress', 'submitted', 'created');
        $explicitError = (isset($j['success']) && $j['success'] === false) || (isset($j['error']) && $j['error'] && !$orderId);
        $failureCode = self::pick($root, array('failure_code'));
        if (in_array($status, $failedWords, true) || ($explicitError && $status === '')
            || ($failureCode !== '' && !in_array($status, array_merge($doneWords, $openWords), true))) {
            $out['outcome'] = self::FAILED;
            if ($failureCode !== '' && strpos($out['message'], $failureCode) === false) {
                $out['message'] = trim($failureCode . ': ' . $out['message'], ': ');
            }
        } elseif ($code !== '' || $pin !== '') {
            $out['outcome'] = self::DELIVERED;
        } elseif ($direct && in_array($status, $doneWords, true)) {
            $out['outcome'] = self::DELIVERED; // direct top-up: credited to the player, no code
        } elseif (in_array($status, $doneWords, true)) {
            if (!$this->cfg->isLive() && isset($result['sandbox']) && $result['sandbox'] === true) {
                // The provider sandbox simulates fulfilment and returns no code: a visible TEST placeholder
                // lets the whole purchase be tested end to end. Never in live mode.
                $out['outcome'] = self::DELIVERED;
                $out['code'] = 'TEST-SANDBOX-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $orderId), -12));
                $out['message'] = 'Sandbox simulated delivery (not a real voucher)';
            } else {
                // Completed at the provider but no code we can show the customer: never refund (it was
                // paid for), never mark delivered without a code. The admin is alerted to resolve it.
                $out['completed_without_code'] = true;
                $out['message'] = substr('provider completed the order but returned no voucher code. ' . $out['message'], 0, 240);
            }
        }
        return $out;
    }

    private static function result(string $outcome, ?string $orderId, ?string $code, ?string $serial, ?string $pin, ?string $expires, string $message): array
    {
        return array('outcome' => $outcome, 'provider_order_id' => $orderId, 'code' => $code, 'serial' => $serial, 'pin' => $pin, 'expires' => $expires, 'message' => trim($message));
    }

    private static function message(array $j): string
    {
        foreach (array('message', 'msg', 'error_description', 'detail') as $k) {
            if (isset($j[$k]) && is_string($j[$k]) && $j[$k] !== '') {
                return substr($j[$k], 0, 240);
            }
        }
        if (isset($j['error'])) {
            if (is_string($j['error'])) {
                return substr($j['error'], 0, 240);
            }
            if (is_array($j['error']) && isset($j['error']['message']) && is_string($j['error']['message'])) {
                return substr($j['error']['message'], 0, 240);
            }
        }
        if (isset($j['errors'][0]['message']) && is_string($j['errors'][0]['message'])) {
            return substr($j['errors'][0]['message'], 0, 240);
        }
        return '';
    }

    // ------------------------------------------------------------------ transport

    private function request(string $method, string $path, ?array $body, string $title, ?string $ref, array $extraHeaders = array(), array $opts = array()): HttpResponse
    {
        $json = $body === null ? null : json_encode($body, JSON_UNESCAPED_SLASHES);
        $authHeader = $this->cfg->extra('auth_header') ?: 'Authorization';
        $key = $this->cfg->cred('api_key');
        $headers = array('Accept' => 'application/json');
        if (strcasecmp($authHeader, 'Authorization') === 0) {
            $headers['Authorization'] = stripos($key, 'Bearer ') === 0 ? $key : 'Bearer ' . $key;
        } else {
            $headers[$authHeader] = $key;
        }
        $secret = $this->cfg->cred('api_secret', false);
        if ($secret !== '') {
            $ts = (string) time();
            $headers['X-Timestamp'] = $ts;
            $headers['X-Signature'] = hash_hmac('sha256', $ts . '.' . ($json === null ? '' : $json), $secret);
        }
        if ($json !== null) {
            $headers['Content-Type'] = 'application/json';
        }
        $headers = array_merge($headers, $extraHeaders);
        return HttpClient::request($method, $this->cfg->url($path), $headers, $json, array_merge(array(
            'timeout' => 45, 'log_type' => 'popular_voucher', 'log_title' => $title, 'log_ref' => $ref,
            'redact' => array('code', 'pin', 'serial'),
        ), $opts));
    }

    /** @return array<string,mixed> decoded JSON; throws on transport failure, 5xx or non-JSON */
    private function call(string $method, string $path, ?array $body, string $title, ?string $ref, array $opts = array()): array
    {
        $resp = $this->request($method, $path, $body, $title, $ref, array(), $opts);
        $j = $resp->json();
        if ($resp->errno !== 0 || !is_array($j) || $resp->status >= 500) {
            throw new GatewayException("Voucher provider $title failed: HTTP {$resp->status} {$resp->error}", 'The voucher service is unavailable right now. Please try again later.');
        }
        if ($resp->status >= 400) {
            throw new GatewayException("Voucher provider $title refused: HTTP {$resp->status} " . self::message($j), 'The voucher service is unavailable right now. Please try again later.');
        }
        return $j;
    }

    private static function pick(array $a, array $keys): string
    {
        foreach ($keys as $k) {
            if (isset($a[$k]) && !is_array($a[$k]) && !is_bool($a[$k]) && (string) $a[$k] !== '') {
                return trim((string) $a[$k]);
            }
        }
        return '';
    }

    /** Demo catalog for sandbox mock mode. Prices are illustrative only. */
    public static function mockCatalog(): array
    {
        $p = function ($code, $name, $cat, $face, $price) {
            $direct = strpos($code, '-UID-') !== false;
            return array('code' => $code, 'name' => $name, 'category' => $cat, 'brand' => $cat, 'country_code' => 'bd', 'country_name' => 'Bangladesh',
                'face_value' => $face, 'price' => $price, 'currency' => 'BDT', 'thumbnail' => null, 'in_stock' => true,
                'delivery_type' => $direct ? 'player_id' : 'code', 'delivery_schema' => null, 'id_label' => 'Player ID', 'id_pattern' => '^[0-9]{5,20}$');
        };
        return array(
            $p('FF-UID-100', 'Free Fire 100 Diamonds (Direct to Player ID)', 'Free Fire', '100 Diamonds', '88.00'),
            $p('PUBG-UID-60', 'PUBG Mobile 60 UC (Direct to Player ID)', 'PUBG Mobile', '60 UC', '98.00'),
            $p('FF-100', 'Free Fire 100 Diamonds', 'Free Fire', '100 Diamonds', '85.00'),
            $p('FF-310', 'Free Fire 310 Diamonds', 'Free Fire', '310 Diamonds', '255.00'),
            $p('FF-520', 'Free Fire 520 Diamonds', 'Free Fire', '520 Diamonds', '420.00'),
            $p('PUBG-60', 'PUBG Mobile 60 UC', 'PUBG Mobile', '60 UC', '95.00'),
            $p('PUBG-325', 'PUBG Mobile 325 UC', 'PUBG Mobile', '325 UC', '470.00'),
            $p('PUBG-660', 'PUBG Mobile 660 UC', 'PUBG Mobile', '660 UC', '930.00'),
            $p('GPLAY-500', 'Google Play Gift Card', 'Google Play', '500 BDT', '500.00'),
            $p('GPLAY-1000', 'Google Play Gift Card', 'Google Play', '1000 BDT', '1000.00'),
            $p('NETFLIX-1M', 'Netflix Mobile - 1 Month', 'OTT Subscription', '1 Month', '350.00'),
            $p('HOICHOI-1M', 'Hoichoi Premium - 1 Month', 'OTT Subscription', '1 Month', '99.00'),
        );
    }
}
