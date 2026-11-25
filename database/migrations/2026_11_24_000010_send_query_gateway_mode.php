<?php
/**
 * Records whether a recharge was sent to the provider's sandbox or live environment.
 * The status poller only enquires rows from the CURRENT mode: a sandbox lookup of a live
 * reference answers "not found", which must never be mistaken for a failure and refunded.
 */
use Midhub\Migration\Schema;

return function () {
    $a = Schema::addColumn('send_query', 'gateway_mode', "ENUM('sandbox','live') NULL AFTER `gateway`");
    return $a ? 'added send_query.gateway_mode' : 'already present';
};
