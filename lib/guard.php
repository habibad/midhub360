<?php
/**
 * Access guards for standalone scripts that sit outside the admin/ login flow
 * (ad-hoc DB tools in the web root, status pollers, cron endpoints).
 *
 * Usage, first line of the script:
 *   require_once __DIR__ . '/lib/guard.php'; midhub_guard_super_admin();
 *   require_once __DIR__ . '/../lib/guard.php'; midhub_guard_cli_or_super_admin();
 */
require_once __DIR__ . '/bootstrap.php';

/** Allow only a logged-in, active Super Admin (level 5). Anyone else gets 403. */
function midhub_guard_super_admin()
{
    \Midhub\Security\AdminAuth::requireSuperAdmin();
}

/**
 * Allow CLI (cron: `php script.php`), a logged-in Super Admin, or an HTTP cron caller
 * presenting CRON_TOKEN from .env in the X-Cron-Token header.
 */
function midhub_guard_cli_or_super_admin()
{
    if (PHP_SAPI === 'cli') {
        return;
    }
    $expected = (string) env('CRON_TOKEN', '');
    $given = isset($_SERVER['HTTP_X_CRON_TOKEN']) ? (string) $_SERVER['HTTP_X_CRON_TOKEN'] : '';
    if ($expected !== '' && strlen($expected) >= 24 && $given !== '' && hash_equals($expected, $given)) {
        return;
    }
    \Midhub\Security\AdminAuth::requireSuperAdmin();
}

/** CLI only. */
function midhub_guard_cli()
{
    if (PHP_SAPI !== 'cli') {
        http_response_code(404);
        exit('Not found');
    }
}
