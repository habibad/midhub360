<?php
/**
 * Midhub360 core bootstrap.
 *
 * Loads environment config + the shared mysqli connection ($connect) and registers
 * the autoloader for the namespaced core library (lib/Midhub/...).
 *
 * The core library is namespaced (Midhub\...) so it can never collide with the
 * legacy global classes in admin/class and customer/class (Paywell, Payment, Api, ...).
 *
 * Compatible with PHP 7.4 (production) and PHP 8.x (local development).
 */

if (!defined('MIDHUB_ROOT')) {
    define('MIDHUB_ROOT', dirname(__DIR__));
}

require_once MIDHUB_ROOT . '/config/env.php';
require_once MIDHUB_ROOT . '/db.php';

if (!defined('MIDHUB_LIB_AUTOLOAD')) {
    define('MIDHUB_LIB_AUTOLOAD', true);
    spl_autoload_register(function ($class) {
        if (strpos($class, 'Midhub\\') !== 0) {
            return;
        }
        $path = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
        if (is_file($path)) {
            require_once $path;
        }
    });
}

if (!defined('MIDHUB_TZ_SET')) {
    define('MIDHUB_TZ_SET', true);
    // All gateway timestamps (Nagad datetime, invoice numbers) use Bangladesh time.
    date_default_timezone_set('Asia/Dhaka');
}
