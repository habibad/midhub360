<?php
/** Self-PIN attempt tracking (lockout) and an audit trail for gateway configuration changes. */
use Midhub\Db;

return function () {
    Db::raw("CREATE TABLE IF NOT EXISTS `security_pin_attempts` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `success` TINYINT(1) NOT NULL DEFAULT 0,
      `ip` VARCHAR(45) NULL,
      `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      KEY `idx_user_time` (`user_id`, `attempted_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    Db::raw("CREATE TABLE IF NOT EXISTS `gateway_audit_log` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `gateway_slug` VARCHAR(50) NOT NULL,
      `admin_id` INT NOT NULL,
      `admin_user` VARCHAR(50) NOT NULL,
      `action` VARCHAR(50) NOT NULL,
      `changes` TEXT NULL COMMENT 'JSON list of changed field names (never values)',
      `ip` VARCHAR(45) NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      KEY `idx_slug` (`gateway_slug`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    return '';
};
