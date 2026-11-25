<?php
/**
 * SIM modem: fetch (and atomically claim) the next queued recharge for an operator.
 *   GET pending_status.php?operator=grameenphone   Authorization: Bearer <device token>
 * Response (legacy shape): {"ID","NUMBER","AMOUNT","OPERATOR","SERVICE","TYPE"} or {"status":"error","message":"NOT FOUND"}
 * The row moves to status 4 (waiting) the moment it is handed out, so no other phone gets it.
 */
require __DIR__ . '/_bootstrap.php';

use Midhub\Modem\ModemGateway;

$job = ModemGateway::claimNext($device, modem_param('operator'));
if (!$job) {
    modem_out(array('status' => 'error', 'message' => 'NOT FOUND'));
}
modem_out(array(
    'ID' => (string) $job['id'],
    'NUMBER' => (string) $job['phone'],
    'AMOUNT' => (string) $job['balance'],
    'OPERATOR' => (string) $job['operator'],
    'SERVICE' => 'recharge',
    'TYPE' => (string) $job['load_type'],
));
