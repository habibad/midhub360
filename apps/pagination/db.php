<?php
// SQL server connection information
require_once dirname(dirname(__DIR__)) . '/config/env.php';

global $sql_details;
$sql_details = array(
    'user' => DB_USER,
    'pass' => DB_PASS,
    'db'   => DB_NAME,
    'host' => DB_HOST
);
?>
