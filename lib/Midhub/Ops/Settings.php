<?php
namespace Midhub\Ops;

use Midhub\Db;

/** Platform-wide switches stored in platform_settings. */
final class Settings
{
    public static function get(string $key, string $default = ''): string
    {
        $v = Db::scalar('SELECT setting_value FROM platform_settings WHERE setting_key = ?', 's', array($key));
        return $v === null ? $default : (string) $v;
    }

    public static function set(string $key, string $value, string $by): void
    {
        Db::exec('INSERT INTO platform_settings (setting_key, setting_value, updated_by) VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)', 'sss', array($key, $value, $by));
    }
}
