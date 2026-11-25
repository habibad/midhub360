<?php
/**
 * Midhub360 database migration runner (CLI only).
 *
 *   php database/migrate.php            run all pending migrations
 *   php database/migrate.php --status   list applied / pending migrations
 *
 * On the production server run it as the web user so an auto-generated encryption key
 * file (if APP_KEY is not set in .env) is readable by PHP-FPM:
 *   sudo -u www-data php /var/www/midhub360.com/database/migrate.php
 *
 * Each migration is a PHP file in database/migrations returning function(): string.
 * Migrations are idempotent, so re-running a failed one is safe.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\Db;

Db::raw("CREATE TABLE IF NOT EXISTS `schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `migration` VARCHAR(190) NOT NULL UNIQUE,
  `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$files = glob(__DIR__ . '/migrations/*.php');
sort($files);
$applied = array();
foreach (Db::all('SELECT migration FROM schema_migrations') as $r) {
    $applied[$r['migration']] = true;
}

$statusOnly = in_array('--status', $argv, true);
echo "Database: " . DB_NAME . " @ " . DB_HOST . " (" . APP_ENV . ")\n";

$pending = 0;
foreach ($files as $file) {
    $name = basename($file, '.php');
    if (isset($applied[$name])) {
        if ($statusOnly) {
            echo "  [applied] $name\n";
        }
        continue;
    }
    $pending++;
    if ($statusOnly) {
        echo "  [pending] $name\n";
        continue;
    }
    echo "-> $name ... ";
    try {
        $fn = require $file;
        $msg = $fn();
        Db::insert('INSERT INTO schema_migrations (migration) VALUES (?)', 's', array($name));
        echo "OK" . ($msg ? " ($msg)" : '') . "\n";
    } catch (\Throwable $e) {
        echo "FAILED\n   " . $e->getMessage() . "\n";
        echo "Stopping. Fix the issue and re-run; completed migrations are not repeated.\n";
        exit(1);
    }
}
if (!$statusOnly) {
    echo $pending === 0 ? "Nothing to migrate.\n" : "Done.\n";
}
