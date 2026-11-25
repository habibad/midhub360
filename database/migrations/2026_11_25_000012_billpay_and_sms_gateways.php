<?php
/**
 * Adds two admin-managed providers:
 *  - billpay : utility bill aggregator (shl.com.bd by default). Seeded ACTIVE in sandbox with
 *              the sandbox token the legacy code hardcoded, so bill fetch keeps working exactly
 *              as before. Payment defaults to MANUAL settlement (debit + admin queue) until the
 *              admin verifies the aggregator's payment endpoint and switches it to API.
 *  - sms     : SMS gateway for admin phone alerts (low balance, circuit breaker, deposit review).
 */
use Midhub\Crypto;
use Midhub\Db;
use Midhub\Gateway\GatewayRegistry;

return function () {
    Db::raw("ALTER TABLE payment_gateways MODIFY `category` ENUM('deposit','recharge','voucher','billpay','notify') NOT NULL DEFAULT 'deposit'");
    $seeded = array();
    foreach (array('billpay' => array(80, 'active', array('token' => 'randomToken')), 'sms' => array(90, 'inactive', null)) as $slug => $cfg) {
        if (Db::scalar('SELECT id FROM payment_gateways WHERE gateway_slug = ?', 's', array($slug))) {
            continue;
        }
        $def = GatewayRegistry::get($slug);
        $extra = array();
        foreach ($def['extra'] as $k => $f) {
            $extra[$k] = isset($f['default']) ? $f['default'] : '';
        }
        Db::insert(
            "INSERT INTO payment_gateways (gateway_slug, gateway_name, category, status, mode, sandbox_base_url, live_base_url, sandbox_credentials, extra_config, min_amount, max_amount, sort_order, updated_by)
             VALUES (?, ?, ?, ?, 'sandbox', ?, ?, ?, ?, 1, 50000, ?, 'migration')",
            'ssssssssi',
            array($slug, $def['name'], $def['category'], $cfg[1], $def['sandbox_base_url'], $def['live_base_url'],
                $cfg[2] ? Crypto::encryptJson($cfg[2]) : null, json_encode($extra, JSON_UNESCAPED_SLASHES), $cfg[0])
        );
        $seeded[] = $slug;
    }
    return $seeded ? 'seeded ' . implode(', ', $seeded) : 'already present';
};
