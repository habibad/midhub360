<?php
/**
 * Popular Vouchers Catalogue & Distributor API v1.1 catalog fields.
 *  - brand / country / currency / thumbnail for browsing 14k+ products
 *  - delivery_schema: the provider's per-product delivery fields (e.g. DestNo, User), sent with the order
 *  - markup_pct: auto-pricing; when set, sell price follows provider cost on every sync
 *  - wider labels / player ids (schema allows up to 128 characters)
 */
use Midhub\Db;
use Midhub\Migration\Schema;

return function () {
    $n = array();
    $cols = array(
        'brand' => "VARCHAR(120) NULL AFTER `category`",
        'country_code' => "VARCHAR(8) NULL AFTER `brand`",
        'country_name' => "VARCHAR(80) NULL AFTER `country_code`",
        'currency' => "VARCHAR(8) NULL AFTER `sell_price`",
        'markup_pct' => "DECIMAL(6,2) NULL COMMENT 'auto price = cost + pct' AFTER `currency`",
        'thumbnail_url' => "VARCHAR(255) NULL",
        'delivery_schema' => "TEXT NULL COMMENT 'provider delivery fields (JSON)'",
    );
    foreach ($cols as $c => $def) {
        if (Schema::addColumn('voucher_products', $c, $def)) {
            $n[] = "voucher_products.$c";
        }
    }
    if (Schema::columnType('voucher_products', 'player_id_label') !== 'varchar(80)') {
        Db::raw("ALTER TABLE voucher_products MODIFY `player_id_label` VARCHAR(80) NOT NULL DEFAULT 'Player ID'");
        $n[] = 'player_id_label widened';
    }
    if (Schema::columnType('voucher_products', 'player_id_pattern') !== 'varchar(160)') {
        Db::raw("ALTER TABLE voucher_products MODIFY `player_id_pattern` VARCHAR(160) NOT NULL DEFAULT '^[0-9]{5,20}$'");
        $n[] = 'player_id_pattern widened';
    }
    if (Schema::columnType('voucher_orders', 'player_id') !== 'varchar(128)') {
        Db::raw('ALTER TABLE voucher_orders MODIFY `player_id` VARCHAR(128) NULL');
        $n[] = 'voucher_orders.player_id widened';
    }
    foreach (array('idx_vp_browse' => '(`provider`, `active`, `category`)', 'idx_vp_country' => '(`country_code`)', 'idx_vp_synced' => '(`provider`, `synced_at`)') as $ix => $def) {
        if (Schema::addIndex('voucher_products', $ix, "INDEX `$ix` $def")) {
            $n[] = $ix;
        }
    }
    return $n ? implode(', ', $n) : 'nothing to do';
};
