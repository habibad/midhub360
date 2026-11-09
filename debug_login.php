<?php
/**
 * Midhub360 Authentication Diagnostics
 * Access via browser: https://midhub360.com/debug_login.php
 * Or via SSH CLI: php debug_login.php
 */

require_once __DIR__ . '/config/env.php';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$_SESSION['debug_test_key'] = "midhub_session_ok_" . time();
$sessId = session_id();

header('Content-Type: text/plain; charset=utf-8');

echo "=== MIDHUB360 DIAGNOSTIC REPORT ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . " (" . php_sapi_name() . ")\n";
echo "Server User: " . (function_exists('posix_getpwuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? 'N/A') : get_current_user()) . "\n\n";

// 1. ENVIRONMENT & DB CHECK
echo "--- 1. DATABASE & CONFIG CHECK ---\n";
echo "IS_LOCAL: " . (IS_LOCAL ? 'true' : 'false') . "\n";
echo "APP_ENV: " . APP_ENV . "\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_NAME: " . DB_NAME . "\n";

try {
    $conn = midhub_get_db_connection();
    echo "[PASS] Database Connected Successfully!\n";

    // Check user newyork24
    $res = mysqli_query($conn, "SELECT id, user, pass, level, active FROM user WHERE user='newyork24'");
    if ($res && mysqli_num_rows($res) > 0) {
        $u = mysqli_fetch_assoc($res);
        echo "[PASS] User 'newyork24' Found:\n";
        echo "  - ID: " . $u['id'] . "\n";
        echo "  - Level: " . $u['level'] . " (Expected: 5)\n";
        echo "  - Active: " . $u['active'] . " (Expected: 1)\n";
        echo "  - Hash: " . $u['pass'] . "\n";
        $md5_match = ($u['pass'] === md5('123456'));
        echo "  - Password match for '123456': " . ($md5_match ? "[YES - MATCH!]" : "[NO - MISMATCH!]") . "\n";
    } else {
        echo "[FAIL] User 'newyork24' NOT FOUND in user table!\n";
    }

    // Check user_blocklist
    $res_block = mysqli_query($conn, "SELECT * FROM user_blocklist WHERE username='newyork24'");
    $is_blocked = ($res_block && mysqli_num_rows($res_block) > 0);
    echo "  - Blocklist Status: " . ($is_blocked ? "[BLOCKED!]" : "[CLEAN - NOT BLOCKED]") . "\n";

    // Check settings country_block
    $res_settings = mysqli_query($conn, "SELECT country_block FROM settings WHERE id='1'");
    if ($res_settings && $s = mysqli_fetch_assoc($res_settings)) {
        echo "  - Country Block Setting: " . $s['country_block'] . "\n";
    }

} catch (Throwable $e) {
    echo "[FAIL] Database Connection Error: " . $e->getMessage() . "\n";
}

// 2. SESSION WRITE & READ PERSISTENCE CHECK
echo "\n--- 2. SESSION PERSISTENCE CHECK ---\n";
$savePath = session_save_path();
if (empty($savePath)) {
    $savePath = sys_get_temp_dir();
}
echo "Session Save Path: " . $savePath . "\n";
echo "Save Path Exists: " . (file_exists($savePath) ? "YES" : "NO") . "\n";
echo "Save Path Writable: " . (is_writable($savePath) ? "YES" : "NO") . "\n";
echo "Active Session ID: " . ($sessId ?: "NONE") . "\n";
echo "Active Session Value: " . ($_SESSION['debug_test_key'] ?? 'NONE') . "\n";

// 3. SERVER HEADERS
echo "\n--- 3. SERVER & CLIENT HEADERS ---\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'N/A') . "\n";
echo "HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'N/A') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";

echo "\n=== END DIAGNOSTIC REPORT ===\n";
