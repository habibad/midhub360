<?php
/**
 * One-time import of the legacy api_paywell row (id 1) into payment_gateways.
 * Copies it into LIVE credentials only when live credentials are still empty, and
 * never activates the gateway. The admin reviews it and switches it on deliberately.
 *
 * Note: the legacy Paywell classes hardcoded an api_key/encryption_key pair in source.
 * Those are NOT imported (their environment is unknown). Enter the correct keys in
 * Admin > Gateway Settings > Paywell, and rotate them with Paywell if they were live keys.
 */
use Midhub\Crypto;
use Midhub\Db;
use Midhub\Migration\Schema;

return function () {
    if (!Schema::hasTable('api_paywell')) {
        return 'no legacy table';
    }
    $legacy = Db::one("SELECT * FROM api_paywell WHERE id = 1 AND company = 'Paywell' LIMIT 1");
    if (!$legacy || (string) $legacy['username'] === '' || (string) $legacy['username'] === '0') {
        return 'no legacy Paywell credentials to import';
    }
    $row = Db::one("SELECT live_credentials, live_base_url FROM payment_gateways WHERE gateway_slug = 'paywell'");
    if (!$row) {
        return 'paywell gateway row missing';
    }
    if (!empty(Crypto::decryptJson($row['live_credentials']))) {
        return 'live credentials already set; skipped';
    }
    $clean = function ($v) {
        $v = (string) $v;
        return $v === '0' ? '' : $v;
    };
    $creds = array(
        'username' => $clean($legacy['username']),
        'password' => $clean($legacy['password']),
        'api_username' => $clean($legacy['apiusername']),
        'api_password' => $clean($legacy['apipassword']),
        'api_key' => $clean($legacy['ap_key']),
        'encryption_key' => $clean($legacy['encryption_key']),
    );
    $base = $clean($legacy['base_url']);
    Db::exec(
        "UPDATE payment_gateways SET live_credentials = ?, live_base_url = IF(live_base_url = '' OR live_base_url IS NULL, ?, live_base_url), updated_by = 'migration' WHERE gateway_slug = 'paywell'",
        'ss', array(Crypto::encryptJson($creds), $base)
    );
    return 'imported legacy Paywell credentials into LIVE (gateway left inactive)';
};
