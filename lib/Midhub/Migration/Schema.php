<?php
namespace Midhub\Migration;

use Midhub\Db;

/**
 * Idempotent schema helpers that work on both MySQL 8 and MariaDB 10.x
 * (MySQL lacks "ADD COLUMN IF NOT EXISTS", so we check information_schema first).
 */
final class Schema
{
    public static function hasTable(string $table): bool
    {
        return (int) Db::scalar('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', 's', array($table)) > 0;
    }

    public static function hasColumn(string $table, string $column): bool
    {
        return (int) Db::scalar('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', 'ss', array($table, $column)) > 0;
    }

    public static function hasIndex(string $table, string $index): bool
    {
        return (int) Db::scalar('SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?', 'ss', array($table, $index)) > 0;
    }

    public static function columnType(string $table, string $column): string
    {
        return strtolower((string) Db::scalar('SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', 'ss', array($table, $column)));
    }

    public static function engine(string $table): string
    {
        return strtolower((string) Db::scalar('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', 's', array($table)));
    }

    public static function addColumn(string $table, string $column, string $definition): bool
    {
        if (self::hasColumn($table, $column)) {
            return false;
        }
        Db::raw("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        return true;
    }

    public static function addIndex(string $table, string $index, string $definition): bool
    {
        if (self::hasIndex($table, $index)) {
            return false;
        }
        Db::raw("ALTER TABLE `$table` ADD $definition");
        return true;
    }
}
