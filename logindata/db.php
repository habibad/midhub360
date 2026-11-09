<?php
/**
 * Login Data Database Connection
 * Auto-detects Localhost vs Live Server via root db.php
 */
require dirname(__DIR__) . '/db.php';

global $conn, $connect, $server_name, $db_user, $db_password, $db_name;
$conn = $connect;
$servername = $server_name;
$username   = $db_user;
$password   = $db_password;
$dbname     = $db_name;
?>
