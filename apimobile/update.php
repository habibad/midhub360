<?php
/** SIM modem (legacy step): mark a recharge as being processed.  update.php?rid=<ID> */
require __DIR__ . '/_bootstrap.php';

use Midhub\Modem\ModemGateway;

$rid = modem_param('rid');
if (!ctype_digit($rid)) {
    modem_out(array('RESPONSE' => 'ERROR', 'message' => 'INVALID REQUEST'), 400);
}
modem_out(array('RESPONSE' => ModemGateway::markWaiting($device, (int) $rid) ? 'SUCCESS' : 'ERROR'));
