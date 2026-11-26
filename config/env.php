<?php
/**
 * Midhub360 Environment & Configuration Manager
 * Automatically synchronizes Localhost (XAMPP) & Live Server (Production)
 */

if (!defined('MIDHUB_ENV_LOADED')) {
    define('MIDHUB_ENV_LOADED', true);

    // 0a. Harden the session cookie before any session_start():
    //     HttpOnly (no JS access), SameSite=Lax (blocks cross-site POST forgery),
    //     Secure when served over HTTPS. Strict mode rejects uninitialized session IDs.
    if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli' && !headers_sent()) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        @ini_set('session.use_strict_mode', '1');
        @ini_set('session.cookie_httponly', '1');
        session_set_cookie_params(array(
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ));
    }

    // 0. Auto-fix session storage path if missing or non-writable
    if (session_status() === PHP_SESSION_NONE) {
        $currSavePath = ini_get('session.save_path');
        if (empty($currSavePath) || !@is_dir($currSavePath) || !@is_writable($currSavePath)) {
            $candidatePaths = [
                sys_get_temp_dir(),
                '/tmp',
                dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sessions'
            ];
            foreach ($candidatePaths as $p) {
                if (!empty($p) && @is_dir($p) && @is_writable($p)) {
                    @ini_set('session.save_path', $p);
                    break;
                }
            }
        }
    }

    // 1. Check for .env file in project root
    $rootDir = dirname(__DIR__);
    $envFile = $rootDir . DIRECTORY_SEPARATOR . '.env';
    $envData = [];

    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // substr() rather than str_starts_with(): production runs PHP 7.4
            if ($line === '' || substr($line, 0, 1) === '#') {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($k, $v) = explode('=', $line, 2);
                $k = trim($k);
                $v = trim(trim($v), "\"'");
                $envData[$k] = $v;
                if (!array_key_exists($k, $_ENV)) {
                    $_ENV[$k] = $v;
                    putenv("$k=$v");
                }
            }
        }
    }

    if (!function_exists('env')) {
        function env($key, $default = null) {
            global $envData;
            if (isset($envData[$key])) {
                return $envData[$key];
            }
            $v = getenv($key);
            if ($v !== false) {
                return $v;
            }
            if (isset($_ENV[$key])) {
                return $_ENV[$key];
            }
            if (isset($_SERVER[$key])) {
                return $_SERVER[$key];
            }
            return $default;
        }
    }

    // 2. Intelligent Environment Detection
    $explicitEnv = env('APP_ENV');
    $httpHost    = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
    $isCli       = (php_sapi_name() === 'cli');
    $isLocalPath = (DIRECTORY_SEPARATOR === '\\' && (stripos(__DIR__, 'xampp') !== false || stripos(__DIR__, 'wamp') !== false));

    $isLocal = false;
    if ($explicitEnv !== null) {
        $isLocal = in_array(strtolower($explicitEnv), ['local', 'development', 'dev', 'test']);
    } else {
        if (!empty($httpHost)) {
            // Web request: check hostname
            if (
                strpos($httpHost, 'localhost') !== false ||
                strpos($httpHost, '127.0.0.1') !== false ||
                $httpHost === '::1'
            ) {
                $isLocal = true;
            } else {
                $isLocal = false;
            }
        } else {
            // CLI or Cron request without HTTP_HOST
            $isLocal = $isLocalPath;
        }
    }

    // 3. Define Environment Constants
    if (!defined('IS_LOCAL')) {
        define('IS_LOCAL', $isLocal);
    }
    if (!defined('APP_ENV')) {
        define('APP_ENV', $isLocal ? 'development' : 'production');
    }

    // 4. Configure Credentials based on environment
    if ($isLocal) {
        // LOCALHOST (XAMPP / Local Testing)
        $defaultHost = 'localhost';
        $defaultUser = 'root';
        $defaultPass = '';
        $defaultDb   = 'midhub-db';
        $defaultPort = 3306;
        $defaultUrl  = 'http://' . ($httpHost ?: 'localhost') . '/midhub360.com';
        $isSandbox   = true;
    } else {
        // LIVE PRODUCTION SERVER (midhub360.com)
        // The password is NEVER written in code (this repository is pushed to GitHub).
        // It comes only from DB_PASS in the server's .env file (chmod 600, owner www-data).
        $defaultHost = 'localhost';
        $defaultUser = 'midhubDBuser';
        $defaultPass = '';
        $defaultDb   = 'midhub-db';
        $defaultPort = 3306;
        $protocol    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $defaultUrl  = $protocol . ($httpHost ?: 'midhub360.com');
        $isSandbox   = false;
    }

    if (!defined('DB_HOST')) define('DB_HOST', env('DB_HOST', $defaultHost));
    if (!defined('DB_USER')) define('DB_USER', env('DB_USER', $defaultUser));
    if (!defined('DB_PASS')) define('DB_PASS', env('DB_PASS', $defaultPass));
    if (!defined('DB_NAME')) define('DB_NAME', env('DB_NAME', $defaultDb));
    if (!defined('DB_PORT')) define('DB_PORT', (int)env('DB_PORT', $defaultPort));
    if (!defined('APP_URL')) define('APP_URL', env('APP_URL', $defaultUrl));
    if (!defined('IS_SANDBOX')) define('IS_SANDBOX', (bool)env('IS_SANDBOX', $isSandbox));

    // 5. Database host candidates, tried in order (shared by the mysqli and PDO helpers)
    if (!function_exists('midhub_db_hosts')) {
        function midhub_db_hosts() {
            $hosts = [DB_HOST];
            if (DB_HOST === 'localhost') {
                $hosts[] = '127.0.0.1';
            } elseif (DB_HOST === '127.0.0.1') {
                $hosts[] = 'localhost';
            }
            // On the live server, if the configured host fails, fall back to the DB server address
            if (!IS_LOCAL) {
                $fallback = (string) env('DB_FALLBACK_HOST', '124.6.224.121');
                if ($fallback !== '' && !in_array($fallback, $hosts, true)) {
                    $hosts[] = $fallback;
                }
            }
            return $hosts;
        }
    }

    // Stops with a clear message when the live server has no database password configured.
    if (!function_exists('midhub_require_db_password')) {
        function midhub_require_db_password() {
            if (!IS_LOCAL && (string) DB_PASS === '') {
                error_log('Midhub360: DB_PASS is not set. Add DB_PASS=... to ' . dirname(__DIR__) . '/.env');
                if (PHP_SAPI === 'cli') {
                    fwrite(STDERR, "DB_PASS is not set in .env (live server). Run: sudo bash deploy/update_live.sh\n");
                    exit(1);
                }
                http_response_code(503);
                die('Server configuration incomplete: the database password is not set in the .env file.');
            }
        }
    }

    // 6. Global helper function to get an active MySQLi connection with automatic failover
    if (!function_exists('midhub_get_db_connection')) {
        function midhub_get_db_connection() {
            midhub_require_db_password();
            $hosts = midhub_db_hosts();

            $lastError = "";
            foreach ($hosts as $host) {
                try {
                    $conn = @mysqli_connect($host, DB_USER, DB_PASS, DB_NAME, DB_PORT);
                    if ($conn) {
                        mysqli_set_charset($conn, "utf8mb4");
                        return $conn;
                    }
                    $lastError = mysqli_connect_error();
                } catch (\Throwable $e) {
                    $lastError = $e->getMessage();
                }
            }

            // If connection fails, output clean error message
            midhub_db_connection_failed($lastError);
        }
    }

    // PDO connection (legacy PDO scripts) with the same credentials and host failover.
    if (!function_exists('midhub_get_pdo')) {
        function midhub_get_pdo() {
            midhub_require_db_password();
            $lastError = '';
            foreach (midhub_db_hosts() as $host) {
                try {
                    $pdo = new PDO('mysql:host=' . $host . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    return $pdo;
                } catch (\Throwable $e) {
                    $lastError = $e->getMessage();
                }
            }
            midhub_db_connection_failed($lastError);
        }
    }

    if (!function_exists('midhub_db_connection_failed')) {
        function midhub_db_connection_failed($lastError) {
            if (PHP_SAPI === 'cli') {
                fwrite(STDERR, "Database connection error: " . $lastError . "\n");
                exit(1);
            }
            if (IS_LOCAL) {
                die("Local Database connection error: " . $lastError . ". Please ensure MySQL is running in XAMPP.");
            }
            error_log("Database connection error: " . $lastError);
            die("Database connection error. Please try again later.");
        }
    }
}
