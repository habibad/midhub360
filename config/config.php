<?php
/**
 * Application Configuration
 * Automatically synchronizes with config/env.php
 */
require_once __DIR__ . '/env.php';

$GLOBALS["config"] = array(
    "mysql" => array(
        "host"     => DB_HOST,
        "user"     => DB_USER,
        "password" => DB_PASS,
        "db"       => DB_NAME,
        "port"     => DB_PORT,
    ),
    "app" => array(
        "env"      => APP_ENV,
        "url"      => APP_URL,
        "is_local" => IS_LOCAL,
        "sandbox"  => IS_SANDBOX,
    )
);
