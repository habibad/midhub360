<?php 
/**
 * Global Database Connection
 * Auto-detects Localhost vs Live Server via config/env.php
 */
require_once __DIR__ . '/config/env.php';

global $connect, $server_name, $db_user, $db_password, $db_name;

$server_name = DB_HOST;
$db_user     = DB_USER;
$db_password = DB_PASS;
$db_name     = DB_NAME;

if (!isset($connect) || !($connect instanceof mysqli) || !@mysqli_ping($connect)) {
    $connect = midhub_get_db_connection();
}
?>
