<?php
/**
 * gateway_transactions: one row per online deposit (add-money) invoice.
 * wallet_ledger:        append-only record of every wallet movement made by the new engine.
 * webhook_events:       de-duplicates provider webhooks (each event is processed once).
 * gateway_tokens:       short-lived OAuth-style tokens (bKash id_token, Paywell token).
 */
use Midhub\Db;

return function () {
    Db::raw("CREATE TABLE IF NOT EXISTS `gateway_transactions` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `invoice_no` VARCHAR(40) NOT NULL UNIQUE,
      `user_id` INT NOT NULL,
      `gateway_slug` VARCHAR(50) NOT NULL,
      `mode` ENUM('sandbox','live') NOT NULL,
      `wallet` ENUM('balance','balance2','bkash_balance') NOT NULL,
      `credit_amount` DECIMAL(14,2) NOT NULL COMMENT 'BDT credited to the wallet',
      `charge_amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'in payable currency',
      `payable_amount` DECIMAL(14,2) NOT NULL COMMENT 'charged at the gateway, in currency',
      `currency` CHAR(3) NOT NULL DEFAULT 'BDT',
      `exchange_rate` DECIMAL(10,4) NULL COMMENT 'USD->BDT rate locked at creation (Stripe)',
      `status` ENUM('initiated','pending','completed','failed','cancelled','review') NOT NULL DEFAULT 'initiated',
      `idempotency_key` VARCHAR(64) NOT NULL,
      `gateway_payment_id` VARCHAR(120) NULL,
      `gateway_trx_id` VARCHAR(120) NULL,
      `checkout_url` TEXT NULL,
      `gateway_state` TEXT NULL COMMENT 'JSON scratch data between checkout steps',
      `failure_reason` VARCHAR(255) NULL,
      `client_ip` VARCHAR(45) NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `completed_at` DATETIME NULL,
      UNIQUE KEY `uniq_user_nonce` (`user_id`, `idempotency_key`),
      KEY `idx_gateway_payment` (`gateway_slug`, `gateway_payment_id`),
      KEY `idx_status_created` (`status`, `created_at`),
      KEY `idx_user` (`user_id`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    Db::raw("CREATE TABLE IF NOT EXISTS `wallet_ledger` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `wallet` ENUM('balance','balance2','bkash_balance') NOT NULL,
      `direction` ENUM('credit','debit') NOT NULL,
      `amount` DECIMAL(14,2) NOT NULL,
      `balance_before` DECIMAL(14,2) NOT NULL,
      `balance_after` DECIMAL(14,2) NOT NULL,
      `ref_type` VARCHAR(30) NOT NULL COMMENT 'deposit, recharge, recharge_refund, voucher, voucher_refund',
      `ref_id` VARCHAR(60) NOT NULL,
      `note` VARCHAR(255) NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      KEY `idx_user_wallet` (`user_id`, `wallet`, `id`),
      KEY `idx_ref` (`ref_type`, `ref_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    Db::raw("CREATE TABLE IF NOT EXISTS `webhook_events` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `gateway_slug` VARCHAR(50) NOT NULL,
      `event_id` VARCHAR(150) NOT NULL,
      `event_type` VARCHAR(80) NULL,
      `ref` VARCHAR(120) NULL,
      `processed` TINYINT(1) NOT NULL DEFAULT 0,
      `result` VARCHAR(255) NULL,
      `received_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY `uniq_event` (`gateway_slug`, `event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    Db::raw("CREATE TABLE IF NOT EXISTS `gateway_tokens` (
      `gateway_slug` VARCHAR(50) NOT NULL,
      `mode` ENUM('sandbox','live') NOT NULL,
      `token` TEXT NOT NULL COMMENT 'encrypted',
      `refresh_token` TEXT NULL COMMENT 'encrypted',
      `expires_at` DATETIME NOT NULL,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`gateway_slug`, `mode`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    return '';
};
