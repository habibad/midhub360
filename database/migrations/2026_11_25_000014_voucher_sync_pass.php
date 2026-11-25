<?php
/**
 * voucher_products.sync_pass: id of the last catalog sync pass that listed the product.
 * After a complete pass, products with a different pass id are no longer listed by the
 * provider and are marked out of stock (timestamps are not precise enough for this).
 */
use Midhub\Migration\Schema;

return function () {
    $n = array();
    if (Schema::addColumn('voucher_products', 'sync_pass', "VARCHAR(20) NULL AFTER `synced_at`")) {
        $n[] = 'voucher_products.sync_pass';
    }
    if (Schema::addIndex('voucher_products', 'idx_vp_pass', 'INDEX `idx_vp_pass` (`provider`, `sync_pass`)')) {
        $n[] = 'idx_vp_pass';
    }
    return $n ? implode(', ', $n) : 'nothing to do';
};
