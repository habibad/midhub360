<?php
/**
 * Admin-panel diagnostics that stay visible even when a page dies half-way.
 *
 * Why: the admin theme's JavaScript is loaded in the footer. When a page stops with a fatal
 * error after the header was printed, the footer never loads and the browser shows only the
 * theme's dark background, with no message. That is what happened on the live server after a
 * `git pull` without running the database migrations.
 *
 * These helpers print fixed-position boxes with inline styles, so they are visible whatever
 * state the theme is in. Only reached from admin/header.php, i.e. after the super-admin login
 * check, so showing the error text is acceptable here.
 *
 * Plain mysqli on purpose: admin/header.php is shared by legacy pages that do not load the
 * Midhub core library. PHP 7.4 compatible.
 */

/** @return string[] migration names present in database/migrations but not applied to this DB */
function midhub_pending_migrations($db)
{
    $files = glob(dirname(__DIR__) . '/database/migrations/*.php');
    if (!$files || !($db instanceof mysqli)) {
        return array();
    }
    $applied = array();
    try {
        $res = @mysqli_query($db, 'SELECT migration FROM schema_migrations');
        if ($res) {
            while ($row = mysqli_fetch_row($res)) {
                $applied[$row[0]] = true;
            }
            mysqli_free_result($res);
        }
    } catch (\Throwable $e) {
        // schema_migrations does not exist yet: every migration is pending
    }
    $pending = array();
    foreach ($files as $f) {
        $name = basename($f, '.php');
        if (!isset($applied[$name])) {
            $pending[] = $name;
        }
    }
    sort($pending);
    return $pending;
}

function midhub_fixed_box($title, $htmlBody, $position)
{
    $edge = $position === 'bottom' ? 'bottom:0' : 'top:0';
    return '<div style="position:fixed;left:0;right:0;' . $edge . ';z-index:2147483647;visibility:visible;opacity:1;'
        . 'background:#fef2f2;color:#7f1d1d;border:2px solid #dc2626;padding:12px 16px;'
        . 'font:14px/1.5 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;max-height:60vh;overflow:auto">'
        . '<strong style="color:#b91c1c">' . $title . '</strong><br>' . $htmlBody . '</div>';
}

/** Red bar at the top of every admin page while the database is behind the code. */
function midhub_pending_migrations_banner(array $pending)
{
    if (!$pending) {
        return '';
    }
    $h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
    $cmd = (defined('IS_LOCAL') && IS_LOCAL)
        ? 'php database/migrate.php'
        : 'cd ' . dirname(__DIR__) . ' &amp;&amp; sudo bash deploy/update_live.sh';
    return midhub_fixed_box(
        'Database update needed: ' . count($pending) . ' migration(s) not applied',
        'The code is newer than this database, so new pages (Gateway Settings, Vouchers, ...) cannot load. '
        . 'Run on the server: <code style="background:#fff;padding:1px 6px;border-radius:4px">' . $cmd . '</code>'
        . '<br><small>Pending: ' . $h(implode(', ', $pending)) . '</small>',
        'top'
    );
}

/** Shows a fatal error / uncaught exception in a visible box instead of a blank dark page. */
function midhub_admin_visible_errors()
{
    register_shutdown_function(function () {
        $e = error_get_last();
        if (!$e || !in_array($e['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR), true)) {
            return;
        }
        $first = strtok((string) $e['message'], "\n"); // uncaught exceptions append a stack trace
        $where = basename((string) $e['file']) . ':' . (int) $e['line'];
        echo midhub_fixed_box(
            'This page stopped with an error',
            htmlspecialchars($first . ' (' . $where . ')', ENT_QUOTES, 'UTF-8')
            . '<br><small>Details are also in the server error log (/var/log/nginx/error.log).</small>',
            'bottom'
        );
    });
}
