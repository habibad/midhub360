<?php
/**
 * Shared bootstrap for the SIM-modem endpoints (apimobile/*.php).
 * Authenticates the device bearer token and provides JSON helpers. Replaces the legacy
 * scripts, which expired in 2023 and authenticated with one shared, SQL-injectable key.
 */
require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\Modem\ModemGateway;

header('Content-Type: application/json');
header('Cache-Control: no-store');

function modem_out(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit();
}

function modem_param(string $name): string
{
    $v = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
    return is_string($v) ? trim($v) : '';
}

$device = ModemGateway::authenticate(ModemGateway::tokenFromRequest());
if ($device === null) {
    modem_out(array('status' => 'error', 'message' => 'Invalid Key'), 401);
}
