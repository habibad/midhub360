<?php
/** Operational alerts (low provider balance, payment amount mismatches, stuck transactions). */
use Midhub\Db;

return function () {
    Db::raw("CREATE TABLE IF NOT EXISTS `admin_alerts` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `alert_key` VARCHAR(120) NOT NULL COMMENT 'de-duplication key, e.g. low_balance:paywell',
      `severity` ENUM('info','warning','critical') NOT NULL DEFAULT 'warning',
      `title` VARCHAR(150) NOT NULL,
      `message` TEXT NOT NULL,
      `resolved` TINYINT(1) NOT NULL DEFAULT 0,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      KEY `idx_key_time` (`alert_key`, `created_at`),
      KEY `idx_open` (`resolved`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    return '';
};
