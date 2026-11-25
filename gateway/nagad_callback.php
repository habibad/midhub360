<?php
/**
 * Nagad merchantCallbackURL (query-free on purpose).
 * Nagad appends ?merchant=...&order_id=...&payment_ref_id=...&status=...&status_code=...
 * The invoice is resolved from order_id / payment_ref_id; see gateway/return.php.
 */
define('MIDHUB_RETURN_GW', 'nagad');
require __DIR__ . '/return.php';
