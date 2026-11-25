<?php
/**
 * Digital goods: admin-priced catalog and the per-customer voucher vault.
 * Voucher codes and PINs are stored encrypted (they are bearer value, like cash).
 */
use Midhub\Db;

return function () {
    Db::raw("CREATE TABLE IF NOT EXISTS `voucher_products` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `provider` VARCHAR(50) NOT NULL DEFAULT 'popular_voucher',
      `product_code` VARCHAR(80) NOT NULL,
      `name` VARCHAR(150) NOT NULL,
      `category` VARCHAR(60) NOT NULL DEFAULT 'Other',
      `face_value` VARCHAR(60) NULL COMMENT 'e.g. 100 Diamonds, 60 UC, USD 10',
      `cost_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'provider price',
      `sell_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'price charged to the customer',
      `in_stock` TINYINT(1) NOT NULL DEFAULT 1,
      `active` TINYINT(1) NOT NULL DEFAULT 0,
      `synced_at` DATETIME NULL,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY `uniq_provider_code` (`provider`, `product_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    Db::raw("CREATE TABLE IF NOT EXISTS `voucher_orders` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `order_no` VARCHAR(40) NOT NULL UNIQUE,
      `user_id` INT NOT NULL,
      `product_id` INT NOT NULL,
      `product_code` VARCHAR(80) NOT NULL,
      `product_name` VARCHAR(150) NOT NULL,
      `price` DECIMAL(12,2) NOT NULL,
      `mode` ENUM('sandbox','live') NOT NULL,
      `status` ENUM('processing','delivered','failed','refunded') NOT NULL DEFAULT 'processing',
      `provider_order_id` VARCHAR(120) NULL,
      `voucher_code` TEXT NULL COMMENT 'encrypted',
      `voucher_serial` TEXT NULL COMMENT 'encrypted',
      `voucher_pin` TEXT NULL COMMENT 'encrypted',
      `expires_on` VARCHAR(30) NULL,
      `failure_reason` VARCHAR(255) NULL,
      `idempotency_key` VARCHAR(64) NOT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `delivered_at` DATETIME NULL,
      `status_checked_at` DATETIME NULL,
      UNIQUE KEY `uniq_user_nonce` (`user_id`, `idempotency_key`),
      KEY `idx_user` (`user_id`, `created_at`),
      KEY `idx_status` (`status`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    return '';
};
