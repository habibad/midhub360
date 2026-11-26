<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

// Credentials come from the project .env (PAYMENT_DB_*), never from code: this repository is
// pushed to GitHub. Minimal reader so CodeIgniter does not need the main app's config.
$__payEnv = array();
$__payEnvFile = dirname(__DIR__, 3) . '/.env';
if (is_file($__payEnvFile)) {
	foreach (file($__payEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $__l) {
		$__l = trim($__l);
		if ($__l === '' || $__l[0] === '#' || strpos($__l, '=') === false) {
			continue;
		}
		list($__k, $__v) = explode('=', $__l, 2);
		$__payEnv[trim($__k)] = trim(trim($__v), "\"'");
	}
}
$__pay = function ($key, $default = '') use ($__payEnv) {
	return isset($__payEnv[$key]) ? $__payEnv[$key] : $default;
};

$db['default'] = array(
	'dsn'	   => '',
	'hostname' => $__pay('PAYMENT_DB_HOST', 'localhost'),
	'port'     => $__pay('PAYMENT_DB_PORT', '3306'),
	'username' => $__pay('PAYMENT_DB_USER', 'aanonda_Pay24'),
	'password' => $__pay('PAYMENT_DB_PASS'),
	'database' => $__pay('PAYMENT_DB_NAME', 'aanonda_Payment24'),
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_unicode_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);