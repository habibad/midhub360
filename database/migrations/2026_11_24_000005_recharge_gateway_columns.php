<?php
/** Track which provider handled a recharge, its reference and polling state. */
use Midhub\Migration\Schema;

return function () {
    $added = array();
    $cols = array(
        'gateway' => "VARCHAR(30) NULL COMMENT 'paywell, eflexiload or NULL for manual queue'",
        'gateway_ref' => "VARCHAR(40) NULL COMMENT 'our unique reference sent to the provider'",
        'gateway_status' => 'VARCHAR(40) NULL',
        'gateway_response' => 'TEXT NULL',
        'status_checked_at' => 'DATETIME NULL',
        'check_attempts' => 'INT NOT NULL DEFAULT 0',
    );
    foreach ($cols as $c => $def) {
        if (Schema::addColumn('send_query', $c, $def)) {
            $added[] = $c;
        }
    }
    Schema::addIndex('send_query', 'uniq_gateway_ref', 'UNIQUE KEY `uniq_gateway_ref` (`gateway_ref`)');
    Schema::addIndex('send_query', 'idx_gateway_status', 'KEY `idx_gateway_status` (`gateway`, `status`)');
    return $added ? 'added: ' . implode(', ', $added) : 'columns already present';
};
