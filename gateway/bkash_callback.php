<?php
/**
 * bKash callbackURL (registered with bKash; query-free on purpose).
 * bKash appends ?paymentID=...&status=success|failure|cancel&apiVersion=...
 * The invoice is resolved from paymentID; see gateway/return.php for the settlement rules.
 */
define('MIDHUB_RETURN_GW', 'bkash');
require __DIR__ . '/return.php';
