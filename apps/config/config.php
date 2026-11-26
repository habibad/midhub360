<?php
error_reporting(E_ALL);
// Database credentials come from the project .env (see config/env.php). Nothing secret lives in
// code: this repository is pushed to GitHub.
require_once dirname(__DIR__, 2) . '/config/env.php';
midhub_require_db_password();
$GLOBALS["config"] = array(
      "mysql" => array(
            // On the live server this legacy panel always connected to the DB server address;
            // set APPS_DB_HOST in .env to change it. Locally it uses DB_HOST (XAMPP).
            "host"     => (string) env('APPS_DB_HOST', IS_LOCAL ? DB_HOST : '124.6.224.121'),
            "user"     => DB_USER,
            "password" => DB_PASS,
            "db"       => DB_NAME,
      ),
);
