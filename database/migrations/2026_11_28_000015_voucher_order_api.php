<?php
/**
 * Popular Vouchers order API, verified with sandbox orders on 2026-11-28:
 *   POST /distributor/transactions (not /distributor/orders, which is 404 and made every order
 *   fail and refund), status GET /distributor/transactions/{id}, delivery values in
 *   "delivery_details" (not "delivery_data"), our order number only in the Idempotency-Key header.
 *
 * Stored settings that still hold the OLD defaults are switched to the verified ones. A value
 * the admin changed to something else is left alone.
 */
use Midhub\Db;

return function () {
    $raw = Db::scalar("SELECT extra_config FROM payment_gateways WHERE gateway_slug = 'popular_voucher'");
    if ($raw === null) {
        return 'no popular_voucher gateway row';
    }
    $cfg = json_decode((string) $raw, true);
    if (!is_array($cfg)) {
        $cfg = array();
    }
    $old = array(
        'order_path' => array('/distributor/orders', '/distributor/transactions'),
        'order_status_path' => array('/distributor/orders/{order_id}', '/distributor/transactions/{order_id}'),
        'order_delivery_field' => array('delivery_data', 'delivery_details'),
        'order_reference_field' => array('reference', ''),
    );
    $changed = array();
    foreach ($old as $key => $pair) {
        if (isset($cfg[$key]) && trim((string) $cfg[$key]) === $pair[0]) {
            if ($pair[1] === '') {
                unset($cfg[$key]); // empty = use the (new, empty) default
            } else {
                $cfg[$key] = $pair[1];
            }
            $changed[] = $key;
        }
    }
    if (!$changed) {
        return 'nothing to do';
    }
    Db::exec("UPDATE payment_gateways SET extra_config = ?, updated_by = 'migration' WHERE gateway_slug = 'popular_voucher'", 's',
        array(json_encode($cfg, JSON_UNESCAPED_SLASHES)));
    return 'popular_voucher: ' . implode(', ', $changed) . ' set to the verified API';
};
