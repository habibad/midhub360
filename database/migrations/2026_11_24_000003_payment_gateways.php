<?php
/**
 * Unified, admin-managed gateway configuration.
 *
 * Differences from the original spec, on purpose:
 *  - sandbox_credentials / live_credentials hold AES-256-GCM encrypted JSON, not plaintext JSON.
 *  - Webhook secrets live inside each mode's credential set (Stripe issues a different
 *    whsec_ per mode), so there is no single shared webhook_secret column.
 *  - extra_config holds non-secret, admin-editable settings (endpoint paths, operator codes).
 */
use Midhub\Crypto;
use Midhub\Db;
use Midhub\Gateway\GatewayRegistry;

return function () {
    Db::raw("CREATE TABLE IF NOT EXISTS `payment_gateways` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `gateway_slug` VARCHAR(50) NOT NULL UNIQUE,
      `gateway_name` VARCHAR(100) NOT NULL,
      `category` ENUM('deposit','recharge','voucher') NOT NULL DEFAULT 'deposit',
      `status` ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
      `mode` ENUM('sandbox','live') NOT NULL DEFAULT 'sandbox',
      `sandbox_base_url` VARCHAR(255) NULL,
      `live_base_url` VARCHAR(255) NULL,
      `sandbox_credentials` TEXT NULL COMMENT 'AES-256-GCM encrypted JSON',
      `live_credentials` TEXT NULL COMMENT 'AES-256-GCM encrypted JSON',
      `extra_config` TEXT NULL COMMENT 'plain JSON, non-secret settings',
      `charge_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
      `min_amount` DECIMAL(10,2) NOT NULL DEFAULT 10.00,
      `max_amount` DECIMAL(10,2) NOT NULL DEFAULT 25000.00,
      `exchange_rate` DECIMAL(10,4) NOT NULL DEFAULT 122.0000,
      `sort_order` INT NOT NULL DEFAULT 0,
      `last_balance` DECIMAL(14,2) NULL,
      `last_balance_at` DATETIME NULL,
      `updated_by` VARCHAR(50) NULL,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Publicly documented sandbox test credentials (safe to ship; editable in the admin panel).
    $sandboxDefaults = array(
        'bkash' => array(
            'app_key' => '4f6o0cjiki2rfm34kfdadl1eqq',
            'app_secret' => '2is7hdktrekvrbljjh44ll3d9l1dtjo4pasmjvs5vl5qr3fug4b',
            'username' => 'sandboxTokenizedUser02',
            'password' => 'sandboxTokenizedUser02@12345',
        ),
        'uddoktapay' => array('api_key' => '982d381360a69d419689740d9f2e26ce36fb7a50'),
    );
    $limits = array(
        'bkash' => array(10, 25000), 'nagad' => array(10, 25000), 'upay' => array(10, 25000),
        'uddoktapay' => array(10, 25000), 'paywell' => array(5, 10000),
        'popular_voucher' => array(10, 25000), 'stripe' => array(5, 1000),
    );

    $order = 0;
    $seeded = 0;
    foreach (GatewayRegistry::all() as $slug => $def) {
        $order += 10;
        if (Db::scalar('SELECT id FROM payment_gateways WHERE gateway_slug = ?', 's', array($slug))) {
            continue;
        }
        $extra = array();
        foreach ($def['extra'] as $k => $f) {
            $extra[$k] = isset($f['default']) ? $f['default'] : '';
        }
        $sandboxCreds = isset($sandboxDefaults[$slug]) ? Crypto::encryptJson($sandboxDefaults[$slug]) : null;
        Db::insert(
            'INSERT INTO payment_gateways (gateway_slug, gateway_name, category, status, mode, sandbox_base_url, live_base_url,
                sandbox_credentials, live_credentials, extra_config, charge_percentage, min_amount, max_amount, exchange_rate, sort_order, updated_by)
             VALUES (?, ?, ?, \'inactive\', \'sandbox\', ?, ?, ?, NULL, ?, 0.00, ?, ?, 122.0000, ?, \'migration\')',
            'sssssssddi',
            array($slug, $def['name'], $def['category'], $def['sandbox_base_url'], $def['live_base_url'],
                $sandboxCreds, json_encode($extra, JSON_UNESCAPED_SLASHES), $limits[$slug][0], $limits[$slug][1], $order)
        );
        $seeded++;
    }
    return "$seeded gateway row(s) seeded (all inactive, sandbox mode)";
};
