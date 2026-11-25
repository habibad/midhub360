<?php
/**
 * The recharge engine checks "is a request for this number already pending?" inside the
 * wallet transaction with SELECT ... FOR UPDATE. Without an index on phone, InnoDB locks
 * every scanned row, serializing ALL recharges system-wide; with it, only that number.
 */
use Midhub\Migration\Schema;

return function () {
    $a = Schema::addIndex('send_query', 'idx_phone_status', 'KEY `idx_phone_status` (`phone`, `status`)');
    $b = Schema::hasTable('bkash_send') ? Schema::addIndex('bkash_send', 'idx_ac_status', 'KEY `idx_ac_status` (`ac_number`, `status`)') : false;
    return ($a ? 'send_query(phone,status) ' : '') . ($b ? 'bkash_send(ac_number,status)' : '');
};
