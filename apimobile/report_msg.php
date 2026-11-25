<?php
/** SIM modem: store the operator's confirmation SMS against the matching recharge (no status change). */
require __DIR__ . '/_bootstrap.php';

use Midhub\Modem\ModemGateway;

$r = ModemGateway::storeReport($device, modem_param('number'), modem_param('amount'), modem_param('from'), modem_param('msg'), modem_param('txnid'), modem_param('simbl'));
modem_out(array('RESPONSE' => $r === 'ok' ? 'SUCCESS' : strtoupper($r)));
