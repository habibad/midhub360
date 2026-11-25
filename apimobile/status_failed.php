<?php
/**
 * SIM modem: report a failed recharge -> refunded to the customer's Recharge wallet once.
 *   status_failed.php?rid=<ID>&reason=...   or   ?number=01XXXXXXXXX&amount=50&reason=...
 */
require __DIR__ . '/_bootstrap.php';

use Midhub\Modem\ModemGateway;

$rid = modem_param('rid');
$r = ModemGateway::failed($device, ctype_digit($rid) ? (int) $rid : null, modem_param('number'), modem_param('amount'), modem_param('reason') ?: 'operator rejected');
modem_out(array('RESPONSE' => $r === 'ok' ? 'REFUNDED' : 'NOT FOUND'));
