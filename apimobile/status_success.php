<?php
/**
 * SIM modem: report a delivered recharge.
 *   status_success.php?rid=<ID>[&txnid=..&msg=..]      (preferred)
 *   status_success.php?number=01XXXXXXXXX&amount=50     (legacy apps)
 * Settles exactly one in-progress row and pays the 5-level commission.
 */
require __DIR__ . '/_bootstrap.php';

use Midhub\Modem\ModemGateway;

$rid = modem_param('rid');
$r = ModemGateway::success($device, ctype_digit($rid) ? (int) $rid : null, modem_param('number'), modem_param('amount'), modem_param('txnid'), modem_param('msg'));
modem_out(array('RESPONSE' => $r === 'ok' ? 'SUCCESS' : 'NOT FOUND'));
