<?php
namespace Midhub\Gateway;

/**
 * Static definitions of every supported provider: credential fields per mode,
 * default base URLs and admin-editable endpoint settings.
 *
 * Credential VALUES are never defined here. They live encrypted in payment_gateways
 * and are entered by the Super Admin. The only defaults are bKash's and UddoktaPay's
 * publicly documented SANDBOX test credentials, seeded by the migration so sandbox
 * testing works out of the box.
 */
final class GatewayRegistry
{
    const SLUGS = array('bkash', 'nagad', 'upay', 'uddoktapay', 'paywell', 'popular_voucher', 'stripe', 'billpay', 'sms');

    /** @return array<string,array<string,mixed>> */
    public static function all(): array
    {
        return array(
            'bkash' => array(
                'name' => 'bKash Tokenized Checkout',
                'category' => 'deposit',
                'currency' => 'BDT',
                'icon' => 'mdi-cellphone-wireless',
                'sandbox_base_url' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta',
                'live_base_url' => 'https://tokenized.pay.bka.sh/v1.2.0-beta',
                'fields' => array(
                    'app_key' => array('label' => 'App Key', 'secret' => false, 'required' => true),
                    'app_secret' => array('label' => 'App Secret', 'secret' => true, 'required' => true),
                    'username' => array('label' => 'Username', 'secret' => false, 'required' => true),
                    'password' => array('label' => 'Password', 'secret' => true, 'required' => true),
                ),
                'extra' => array(),
                'return_url' => true,
                'return_path' => 'gateway/bkash_callback.php',
                'webhook_url' => false,
                'note' => 'Register the Callback URL below with bKash (they reject unregistered domains: error 2049). Tokenized Checkout v1.2.0-beta. Payments are captured with Execute Payment after the customer returns, then confirmed server-to-server with Query Payment. bKash sends no webhook.',
            ),
            'nagad' => array(
                'name' => 'Nagad Merchant (PGW)',
                'category' => 'deposit',
                'currency' => 'BDT',
                'icon' => 'mdi-cellphone-nfc',
                'sandbox_base_url' => 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0',
                'live_base_url' => 'https://api.mynagad.com',
                'fields' => array(
                    'merchant_id' => array('label' => 'Merchant ID', 'secret' => false, 'required' => true),
                    'merchant_number' => array('label' => 'Merchant Account Number', 'secret' => false, 'required' => true, 'help' => '01XXXXXXXXX'),
                    'merchant_private_key' => array('label' => 'Merchant Private Key', 'secret' => true, 'required' => true, 'type' => 'textarea', 'help' => 'RSA private key (PEM or raw base64).'),
                    'nagad_public_key' => array('label' => 'Nagad PG Public Key', 'secret' => false, 'required' => true, 'type' => 'textarea', 'help' => 'RSA public key issued by Nagad (PEM or raw base64).'),
                ),
                'extra' => array(
                    'api_version' => array('label' => 'X-KM-Api-Version header', 'default' => 'v-0.2.0'),
                ),
                'return_url' => true,
                'return_path' => 'gateway/nagad_callback.php',
                'webhook_url' => false,
                'note' => 'Checkout payloads are RSA-encrypted with the Nagad public key and signed SHA256withRSA with your merchant private key. Every payment is confirmed with Verify Payment before crediting.',
            ),
            'upay' => array(
                'name' => 'Upay Merchant',
                'category' => 'deposit',
                'currency' => 'BDT',
                'icon' => 'mdi-wallet-outline',
                'sandbox_base_url' => 'https://uat-pg.upay.systems',
                'live_base_url' => 'https://pg.upaysystem.com',
                'fields' => array(
                    'merchant_id' => array('label' => 'Merchant ID', 'secret' => false, 'required' => true),
                    'merchant_key' => array('label' => 'Merchant Key', 'secret' => true, 'required' => true),
                    'merchant_code' => array('label' => 'Merchant Code', 'secret' => false, 'required' => true),
                    'merchant_name' => array('label' => 'Merchant Name', 'secret' => false, 'required' => true),
                    'merchant_mobile' => array('label' => 'Merchant Mobile', 'secret' => false, 'required' => true),
                    'merchant_city' => array('label' => 'Merchant City', 'secret' => false, 'required' => false, 'default' => 'Dhaka'),
                    'merchant_category_code' => array('label' => 'Merchant Category Code (MCC)', 'secret' => false, 'required' => false),
                ),
                'extra' => array(
                    'auth_path' => array('label' => 'Auth endpoint', 'default' => '/payment/merchant-auth/'),
                    'init_path' => array('label' => 'Payment init endpoint', 'default' => '/payment/merchant-payment-init/'),
                    'status_path' => array('label' => 'Status endpoint ({txn_id} placeholder)', 'default' => '/payment/single-payment-status/{txn_id}/'),
                ),
                'return_url' => true,
                'webhook_url' => false,
                'note' => 'Upay has no public API documentation. Endpoint paths are editable: confirm them against the integration document Upay issues with your merchant account.',
            ),
            'uddoktapay' => array(
                'name' => 'UddoktaPay (MFS Aggregator)',
                'category' => 'deposit',
                'currency' => 'BDT',
                'icon' => 'mdi-bank-transfer',
                'sandbox_base_url' => 'https://sandbox.uddoktapay.com',
                'live_base_url' => '',
                'fields' => array(
                    'api_key' => array('label' => 'API Key', 'secret' => true, 'required' => true),
                ),
                'extra' => array(),
                'return_url' => true,
                'webhook_url' => true,
                'note' => 'Live Base URL is your own UddoktaPay panel domain, e.g. https://pay.yourdomain.com. Webhooks are authenticated with the RT-UDDOKTAPAY-API-KEY header (UddoktaPay does not HMAC-sign them), then re-verified with Verify Payment.',
            ),
            'paywell' => array(
                'name' => 'Paywell (Recharge & Bill Pay)',
                'category' => 'recharge',
                'currency' => 'BDT',
                'icon' => 'mdi-cellphone-arrow-down',
                'sandbox_base_url' => '',
                'live_base_url' => '',
                'fields' => array(
                    'username' => array('label' => 'Reseller Username', 'secret' => false, 'required' => true),
                    'password' => array('label' => 'Reseller Password', 'secret' => true, 'required' => true),
                    'api_username' => array('label' => 'API Username (token auth)', 'secret' => false, 'required' => true),
                    'api_password' => array('label' => 'API Password (token auth)', 'secret' => true, 'required' => true),
                    'api_key' => array('label' => 'API Key', 'secret' => true, 'required' => true),
                    'encryption_key' => array('label' => 'Encryption Key (HMAC)', 'secret' => true, 'required' => true),
                ),
                'extra' => array(
                    'token_path' => array('label' => 'Token endpoint', 'default' => '/Authentication/PaywellAuth/getToken'),
                    'topup_path' => array('label' => 'Top-up endpoint', 'default' => '/Recharge/mobileRecharge/singleTopup'),
                    'enquiry_path' => array('label' => 'Enquiry endpoint', 'default' => '/Recharge/mobileRecharge/rechargeEnquiry', 'help' => 'Confirm against your Paywell API Specification (v2.1.x).'),
                    'balance_path' => array('label' => 'Balance endpoint', 'default' => '/Retailer/RetailerService/retailerBalance'),
                    'operator_codes' => array('label' => 'Operator codes (JSON)', 'default' => '{"grameenphone":"GP","robi":"RB","banglalink":"BL","airtel":"AT","teletalk":"TT","skitto":"SK"}', 'help' => 'Confirm codes against your Paywell spec. "skitto" is used for type 3 (Skitto/Others).'),
                    'success_codes' => array('label' => 'Success status codes', 'default' => '200'),
                    'pending_codes' => array('label' => 'Pending status codes', 'default' => '100,102,202'),
                    'low_balance_threshold' => array('label' => 'Low-balance alert (BDT)', 'default' => '20000'),
                ),
                'return_url' => false,
                'webhook_url' => true,
                'note' => 'A callback is treated only as a trigger: the real outcome is always fetched server-to-server with the enquiry endpoint, so a forged callback cannot settle a recharge. Rows still pending are polled by cron/recharge_status_poller.php.',
            ),
            'popular_voucher' => array(
                'name' => 'Popular Voucher (Digital Goods)',
                'category' => 'voucher',
                'currency' => 'BDT',
                'icon' => 'mdi-ticket-percent-outline',
                'sandbox_base_url' => 'https://medback.popularvouchers.com',
                'live_base_url' => 'https://medback.popularvouchers.com',
                'fields' => array(
                    'api_key' => array('label' => 'API Key', 'secret' => true, 'required' => true),
                    'api_secret' => array('label' => 'API Secret (request signing, optional)', 'secret' => true, 'required' => false),
                    'merchant_id' => array('label' => 'Merchant / Reseller ID', 'secret' => false, 'required' => false),
                ),
                'extra' => array(
                    'catalog_path' => array('label' => 'Catalog endpoint', 'default' => '/distributor/catalog'),
                    'order_path' => array('label' => 'Place order endpoint', 'default' => '/distributor/transactions'),
                    'order_status_path' => array('label' => 'Order status endpoint ({order_id})', 'default' => '/distributor/transactions/{order_id}'),
                    'balance_path' => array('label' => 'Balance endpoint', 'default' => '/distributor/account'),
                    'auth_header' => array('label' => 'API key header name', 'default' => 'Authorization'),
                    'order_product_field' => array('label' => 'Order: product id field', 'default' => 'variant_id', 'help' => 'Catalog v1.1 identifies products by variant_id.'),
                    'order_reference_field' => array('label' => 'Order: extra reference field (optional)', 'default' => '', 'help' => 'Leave empty: our order number already goes in the Idempotency-Key header, which the provider stores as external_id.'),
                    'order_delivery_field' => array('label' => 'Order: delivery fields object', 'default' => 'delivery_details', 'help' => 'Object that carries the product delivery_schema values, e.g. {"DestNo":"..."}. Enter - to send them at the top level.'),
                    'player_id_param' => array('label' => 'Player ID field (products without a delivery schema)', 'default' => 'player_id'),
                    'low_balance_threshold' => array('label' => 'Low distributor balance alert (BDT)', 'default' => '2000', 'help' => '0 disables the alert. Checked every 15 minutes.'),
                ),
                'return_url' => false,
                'webhook_url' => true,
                'note' => 'Popular Vouchers Catalogue & Distributor API v1.1 (Bearer API key). Catalog, account, order (POST /distributor/transactions with Idempotency-Key, delivery_details) and status endpoints are verified against the sandbox. The provider account environment (sandbox/live) is checked: a LIVE store with a SANDBOX key never places orders. Allowlist this server\'s public IP in the distributor portal.',
            ),
            'stripe' => array(
                'name' => 'Stripe (International USD)',
                'category' => 'deposit',
                'currency' => 'USD',
                'icon' => 'mdi-credit-card-outline',
                'sandbox_base_url' => 'https://api.stripe.com',
                'live_base_url' => 'https://api.stripe.com',
                'fields' => array(
                    'publishable_key' => array('label' => 'Publishable Key', 'secret' => false, 'required' => true, 'prefix' => array('sandbox' => 'pk_test_', 'live' => 'pk_live_')),
                    'secret_key' => array('label' => 'Secret Key', 'secret' => true, 'required' => true, 'prefix' => array('sandbox' => array('sk_test_', 'rk_test_'), 'live' => array('sk_live_', 'rk_live_'))),
                    'webhook_secret' => array('label' => 'Webhook Signing Secret', 'secret' => true, 'required' => true, 'prefix' => array('sandbox' => 'whsec_', 'live' => 'whsec_'), 'help' => 'Stripe issues a different whsec_ secret for test and live webhook endpoints.'),
                ),
                'extra' => array(
                    'rate_margin_bdt' => array('label' => 'Safety margin (BDT per USD)', 'default' => '2.00', 'help' => 'Credited rate = exchange rate minus this buffer, to absorb USD/BDT swings.'),
                    'new_user_days' => array('label' => 'New card customer period (days)', 'default' => '3'),
                    'new_user_daily_usd' => array('label' => 'New customer daily limit (USD)', 'default' => '30', 'help' => 'Applies until a customer has a completed card payment older than the period above.'),
                ),
                'return_url' => true,
                'webhook_url' => true,
                'note' => 'Stripe Checkout Session (hosted page: Visa, Mastercard, Apple Pay / Google Pay when enabled in your Stripe dashboard) with 3-D Secure requested on every card. Customers pay in USD; the wallet is credited in BDT at the rate (minus the safety margin) locked when the invoice is created. Min/Max are in USD. Webhook events: checkout.session.completed, checkout.session.async_payment_succeeded, checkout.session.expired.',
            ),
            'billpay' => array(
                'name' => 'Utility Bill Pay Aggregator',
                'category' => 'billpay',
                'currency' => 'BDT',
                'icon' => 'mdi-lightning-bolt-outline',
                'sandbox_base_url' => 'https://shl.com.bd',
                'live_base_url' => 'https://shl.com.bd',
                'fields' => array(
                    'token' => array('label' => 'API Token', 'secret' => true, 'required' => true),
                ),
                'extra' => array(
                    'payment_mode' => array('label' => 'Payment settlement: manual | api', 'default' => 'manual', 'help' => 'manual = wallet is debited and the bill goes to the admin Bill Pay queue; api = paid instantly through the aggregator.'),
                    'fetch_path' => array('label' => 'Bill fetch endpoint', 'default' => '/api/appapi/billpay/fetch/{env}/{biller}'),
                    'pay_path' => array('label' => 'Bill payment endpoint', 'default' => '/api/appapi/billpay/payment/{env}/{biller}', 'help' => 'Unverified: confirm with your aggregator before switching payment_mode to api.'),
                    'status_path' => array('label' => 'Payment status endpoint', 'default' => '/api/appapi/billpay/status/{env}/{biller}'),
                    'token_header' => array('label' => 'Token header name', 'default' => 'token'),
                    'env_sandbox' => array('label' => 'URL {env} value in sandbox', 'default' => 'sandbox'),
                    'env_live' => array('label' => 'URL {env} value in live', 'default' => 'live'),
                    'biller_slugs' => array('label' => 'Biller slug overrides (JSON)', 'default' => '{}', 'help' => 'e.g. {"Akash DTH":"akash-dth","Link3":"link3"} to correct slugs for your aggregator.'),
                ),
                'return_url' => false,
                'webhook_url' => false,
                'note' => 'Bill fetch, payment and status for electricity, water, gas, cable TV and internet billers. Seeded with the shl.com.bd sandbox token the legacy code used, so bill fetch keeps working. Wallet money is never marked as a paid bill unless the aggregator confirms it (api mode) or an admin settles it from the Bill Pay queue (manual mode).',
            ),
            'sms' => array(
                'name' => 'SMS Gateway (admin alerts)',
                'category' => 'notify',
                'currency' => 'BDT',
                'icon' => 'mdi-message-alert-outline',
                'sandbox_base_url' => '',
                'live_base_url' => '',
                'fields' => array(
                    'api_key' => array('label' => 'API Key / Password', 'secret' => true, 'required' => true),
                    'sender_id' => array('label' => 'Sender ID / Masking', 'secret' => false, 'required' => false),
                ),
                'extra' => array(
                    'send_path' => array('label' => 'Send endpoint with placeholders', 'default' => '/api/smsapi?api_key={api_key}&type=text&number={to}&senderid={sender}&message={message}', 'help' => 'Placeholders: {to} {message} {api_key} {sender}. Works with most Bangladeshi bulk-SMS providers.'),
                    'method' => array('label' => 'HTTP method (GET or POST)', 'default' => 'GET'),
                    'admin_phones' => array('label' => 'Admin phone numbers (comma separated)', 'default' => ''),
                    'success_contains' => array('label' => 'Response text that means "sent"', 'default' => ''),
                    'min_severity' => array('label' => 'Send SMS for: critical | warning', 'default' => 'warning'),
                ),
                'return_url' => false,
                'webhook_url' => false,
                'note' => 'Sends System Alerts (low Paywell balance, circuit breaker trips, deposits held for review, stuck transactions) to the admin phones. Test connection sends a test SMS.',
            ),
        );
    }

    /** @return array<string,mixed> */
    public static function get(string $slug): array
    {
        $all = self::all();
        if (!isset($all[$slug])) {
            throw new \InvalidArgumentException('Unknown gateway: ' . $slug);
        }
        return $all[$slug];
    }

    public static function exists(string $slug): bool
    {
        return in_array($slug, self::SLUGS, true);
    }
}
