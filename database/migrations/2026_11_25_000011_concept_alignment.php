<?php
/**
 * Schema for the final-concept features:
 *  - platform_settings      key/value switches (recharge commission on/off, ...)
 *  - recharge_commission_rates + commission_payouts   5-level per-recharge commission (exactly once)
 *  - circuit_breakers       automatic kill switch per provider/operator
 *  - modem_devices          SIM-modem gateway devices with hashed bearer tokens
 *  - billpay_request / voucher_* / gateway_transactions columns for the new flows
 *  - UNIQUE(gateway_slug, gateway_trx_id) so a provider transaction can credit only one invoice
 *  - United Kingdom in country_allow (diaspora markets: US, UK, Canada, Middle East)
 */
use Midhub\Db;
use Midhub\Migration\Schema;

return function () {
    $notes = array();

    Db::raw("CREATE TABLE IF NOT EXISTS `platform_settings` (
      `setting_key` VARCHAR(80) NOT NULL PRIMARY KEY,
      `setting_value` TEXT NULL,
      `updated_by` VARCHAR(50) NULL,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    Db::exec("INSERT IGNORE INTO platform_settings (setting_key, setting_value, updated_by) VALUES ('recharge_commission_enabled', '0', 'migration')");

    Db::raw("CREATE TABLE IF NOT EXISTS `recharge_commission_rates` (
      `level` TINYINT NOT NULL PRIMARY KEY COMMENT 'beneficiary account level 0..4',
      `rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'percent of the recharge amount'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    for ($l = 0; $l <= 4; $l++) {
        Db::exec('INSERT IGNORE INTO recharge_commission_rates (level, rate) VALUES (?, 0.00)', 'i', array($l));
    }

    Db::raw("CREATE TABLE IF NOT EXISTS `commission_payouts` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `source_type` VARCHAR(20) NOT NULL DEFAULT 'recharge',
      `source_id` INT NOT NULL COMMENT 'send_query.id',
      `beneficiary_id` INT NOT NULL,
      `beneficiary_level` TINYINT NOT NULL,
      `rate` DECIMAL(5,2) NOT NULL,
      `base_amount` DECIMAL(14,2) NOT NULL,
      `amount` DECIMAL(14,2) NOT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY `uniq_payout` (`source_type`, `source_id`, `beneficiary_id`),
      KEY `idx_beneficiary` (`beneficiary_id`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    Schema::addIndex('security_pin_attempts', 'idx_ip_time', 'KEY `idx_ip_time` (`ip`, `attempted_at`)');

    Db::raw("CREATE TABLE IF NOT EXISTS `circuit_breakers` (
      `cb_key` VARCHAR(80) NOT NULL PRIMARY KEY,
      `failures` INT NOT NULL DEFAULT 0,
      `opened_until` DATETIME NULL,
      `last_error` VARCHAR(255) NULL,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    Db::raw("CREATE TABLE IF NOT EXISTS `modem_devices` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(80) NOT NULL,
      `token_hash` CHAR(64) NOT NULL UNIQUE COMMENT 'sha256 of the bearer token; the token itself is never stored',
      `token_hint` VARCHAR(12) NOT NULL,
      `operators` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'comma list of operators this device serves, empty = all',
      `active` TINYINT(1) NOT NULL DEFAULT 1,
      `last_seen_at` DATETIME NULL,
      `last_ip` VARCHAR(45) NULL,
      `created_by` VARCHAR(50) NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    Schema::addColumn('send_query', 'modem_device_id', 'INT NULL');

    foreach (array(
        'gateway_mode' => "ENUM('sandbox','live') NULL",
        'gateway_ref' => 'VARCHAR(40) NULL',
        'debited_amount' => 'DECIMAL(14,2) NULL',
        'provider_response' => 'TEXT NULL',
        'paid_at' => 'DATETIME NULL',
        'check_attempts' => 'INT NOT NULL DEFAULT 0',
        'checked_at' => 'DATETIME NULL',
    ) as $c => $def) {
        if (Schema::addColumn('billpay_request', $c, $def)) {
            $notes[] = "billpay_request.$c";
        }
    }
    Schema::addIndex('billpay_request', 'idx_bill_payment_id', 'KEY `idx_bill_payment_id` (`bill_payment_id`)');

    Schema::addColumn('voucher_products', 'delivery_type', "ENUM('code','player_id') NOT NULL DEFAULT 'code'");
    Schema::addColumn('voucher_products', 'player_id_label', "VARCHAR(40) NOT NULL DEFAULT 'Player ID'");
    Schema::addColumn('voucher_products', 'player_id_pattern', "VARCHAR(80) NOT NULL DEFAULT '^[0-9]{5,20}$'");
    Schema::addColumn('voucher_orders', 'player_id', 'VARCHAR(64) NULL');
    Schema::addColumn('voucher_orders', 'deposit_invoice', 'VARCHAR(40) NULL');

    Schema::addColumn('gateway_transactions', 'intent', 'TEXT NULL COMMENT \'JSON: action to run after the credit, e.g. a voucher purchase\'');
    Schema::addColumn('gateway_transactions', 'intent_done_at', 'DATETIME NULL');
    if (!Schema::hasIndex('gateway_transactions', 'uniq_gateway_trx')) {
        // review rows used to carry the duplicate trx id; clear them before enforcing uniqueness
        Db::exec("UPDATE gateway_transactions SET gateway_trx_id = NULL WHERE status <> 'completed'");
        Db::raw('ALTER TABLE gateway_transactions ADD UNIQUE KEY `uniq_gateway_trx` (`gateway_slug`, `gateway_trx_id`)');
        $notes[] = 'unique gateway trx';
    }

    if (Schema::hasTable('country_allow')) {
        Db::exec("INSERT IGNORE INTO country_allow (CountryName, CountryCode) VALUES ('United Kingdom', 'GB')");
    }
    return $notes ? implode(', ', $notes) : 'ok';
};
