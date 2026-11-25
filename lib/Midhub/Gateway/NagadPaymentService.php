<?php
namespace Midhub\Gateway;

use Midhub\Http\HttpClient;
use Midhub\Money;

/**
 * Nagad Merchant Payment Gateway (remote-payment-gateway, API v-0.2.0).
 *
 *   1. Initialize  POST /api/dfs/check-out/initialize/{merchantId}/{orderId}
 *                  sensitiveData = RSA(NagadPublicKey, {merchantId, datetime, orderId, challenge})
 *                  signature     = SHA256withRSA(MerchantPrivateKey, same JSON)
 *                  -> response sensitiveData decrypted with our private key: paymentReferenceId + challenge
 *   2. Complete    POST /api/dfs/check-out/complete/{paymentReferenceId}  -> callBackUrl (redirect customer)
 *   3. Customer returns to merchantCallbackURL with payment_ref_id + status
 *   4. Verify      GET  /api/dfs/verify/payment/{paymentReferenceId}      (authoritative)
 */
final class NagadPaymentService implements DepositGatewayInterface
{
    /** @var GatewayConfig */
    private $cfg;

    public function __construct(GatewayConfig $cfg)
    {
        $this->cfg = $cfg;
    }

    public function createCheckout(array $txn, array $customer): array
    {
        $merchantId = $this->cfg->cred('merchant_id');
        $orderId = $txn['invoice_no'];
        $dateTime = date('YmdHis');
        $challenge = bin2hex(random_bytes(20));

        $sensitive = json_encode(array('merchantId' => $merchantId, 'datetime' => $dateTime, 'orderId' => $orderId, 'challenge' => $challenge));
        $init = $this->post(
            '/api/dfs/check-out/initialize/' . rawurlencode($merchantId) . '/' . rawurlencode($orderId) . '?locale=EN',
            array(
                'accountNumber' => $this->cfg->cred('merchant_number'),
                'dateTime' => $dateTime,
                'sensitiveData' => $this->encrypt($sensitive),
                'signature' => $this->sign($sensitive),
            ),
            'initialize', $orderId
        );
        if (empty($init['sensitiveData']) || empty($init['signature'])) {
            throw new GatewayException('Nagad initialize failed: ' . $this->errorText($init), 'Nagad is unavailable right now. Please try again later.');
        }
        $plain = $this->decrypt((string) $init['sensitiveData']);
        if (!$this->verifySignature($plain, (string) $init['signature'])) {
            throw new GatewayException('Nagad initialize response signature is invalid', 'Nagad returned an invalid response. Please try again.');
        }
        $data = json_decode($plain, true);
        if (empty($data['paymentReferenceId']) || empty($data['challenge'])) {
            throw new GatewayException('Nagad initialize: missing paymentReferenceId');
        }
        $refId = (string) $data['paymentReferenceId'];

        $sensitive2 = json_encode(array(
            'merchantId' => $merchantId,
            'orderId' => $orderId,
            'currencyCode' => '050',
            'amount' => Money::parse($txn['payable_amount']),
            'challenge' => (string) $data['challenge'],
        ));
        $complete = $this->post('/api/dfs/check-out/complete/' . rawurlencode($refId), array(
            'sensitiveData' => $this->encrypt($sensitive2),
            'signature' => $this->sign($sensitive2),
            'merchantCallbackURL' => GatewayConfig::publicUrl('gateway/nagad_callback.php'), // Nagad appends ?order_id=&payment_ref_id=&status=
            'additionalMerchantInfo' => array('invoice' => $orderId),
        ), 'complete', $orderId);

        if (!isset($complete['status']) || $complete['status'] !== 'Success' || empty($complete['callBackUrl'])) {
            throw new GatewayException('Nagad complete failed: ' . $this->errorText($complete), 'Nagad is unavailable right now. Please try again later.');
        }
        return array('redirect_url' => (string) $complete['callBackUrl'], 'gateway_payment_id' => $refId, 'state' => array());
    }

    public function completeReturn(array $txn, array $params): PaymentResult
    {
        $ref = isset($params['payment_ref_id']) ? (string) $params['payment_ref_id'] : '';
        if ($ref !== '' && !hash_equals((string) $txn['gateway_payment_id'], $ref)) {
            return PaymentResult::make(PaymentResult::FAILED, 'Payment reference mismatch.');
        }
        if (isset($params['status']) && strtolower((string) $params['status']) === 'aborted') {
            return PaymentResult::make(PaymentResult::CANCELLED, 'Payment was cancelled.');
        }
        // Never trust the redirect's status: always verify server-to-server.
        return $this->verify($txn);
    }

    public function verify(array $txn): PaymentResult
    {
        if (empty($txn['gateway_payment_id'])) {
            return PaymentResult::make(PaymentResult::FAILED, 'No Nagad payment was created for this invoice.');
        }
        $resp = HttpClient::request('GET', $this->cfg->url('/api/dfs/verify/payment/' . rawurlencode($txn['gateway_payment_id'])), $this->headers(), null, array(
            'timeout' => 30, 'log_type' => 'nagad', 'log_title' => 'verify', 'log_ref' => $txn['invoice_no'],
        ));
        $res = $resp->json();
        if ($res === null) {
            throw new GatewayException("Nagad verify transport error: HTTP {$resp->status} {$resp->error}");
        }
        $status = isset($res['status']) ? (string) $res['status'] : '';
        $r = PaymentResult::make(PaymentResult::PENDING, $status, $res);
        $r->gatewayPaymentId = isset($res['paymentRefId']) ? (string) $res['paymentRefId'] : null;
        $r->gatewayTrxId = isset($res['issuerPaymentRefNo']) ? (string) $res['issuerPaymentRefNo'] : null;
        $r->paidAmount = isset($res['amount']) ? (string) $res['amount'] : null;
        $r->currency = 'BDT';
        $r->invoiceNo = isset($res['orderId']) ? (string) $res['orderId'] : null;
        if ($status === 'Success') {
            $r->status = PaymentResult::COMPLETED;
        } elseif (in_array($status, array('Aborted', 'Cancelled'), true)) {
            $r->status = PaymentResult::CANCELLED;
        } elseif (in_array($status, array('Failed', 'Rejected', 'Expired'), true)) {
            $r->status = PaymentResult::FAILED;
        }
        return $r;
    }

    public function testConnection(): array
    {
        try {
            $this->cfg->cred('merchant_id');
            $this->cfg->cred('merchant_number');
            $priv = openssl_pkey_get_private($this->pem($this->cfg->cred('merchant_private_key'), 'PRIVATE'));
            $pub = openssl_pkey_get_public($this->pem($this->cfg->cred('nagad_public_key'), 'PUBLIC'));
            if (!$priv) {
                return array('ok' => false, 'message' => 'Merchant private key could not be parsed.');
            }
            if (!$pub) {
                return array('ok' => false, 'message' => 'Nagad public key could not be parsed.');
            }
            $probe = HttpClient::request('GET', $this->cfg->url('/'), array(), null, array('timeout' => 10));
            $reach = $probe->neverReachedServer() ? 'NOT reachable (' . $probe->error . ')' : 'reachable';
            return array('ok' => !$probe->neverReachedServer(), 'message' => "Keys parsed OK; Nagad host $reach ({$this->cfg->mode}). A full checkout test needs a real order.");
        } catch (GatewayException $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    // ------------------------------------------------------------------------

    private function headers(): array
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        return array(
            'Content-Type' => 'application/json',
            'X-KM-Api-Version' => $this->cfg->extra('api_version') ?: 'v-0.2.0',
            'X-KM-IP-V4' => $ip,
            'X-KM-Client-Type' => 'PC_WEB',
        );
    }

    /** @return array<string,mixed> */
    private function post(string $path, array $body, string $title, string $ref): array
    {
        $resp = HttpClient::request('POST', $this->cfg->url($path), $this->headers(), $body, array(
            'json' => true, 'timeout' => 30, 'log_type' => 'nagad', 'log_title' => $title, 'log_ref' => $ref,
        ));
        $json = $resp->json();
        if ($json === null) {
            throw new GatewayException("Nagad $title transport error: HTTP {$resp->status} {$resp->error}");
        }
        return $json;
    }

    private function encrypt(string $plain): string
    {
        $key = openssl_pkey_get_public($this->pem($this->cfg->cred('nagad_public_key'), 'PUBLIC'));
        if (!$key || !openssl_public_encrypt($plain, $out, $key, OPENSSL_PKCS1_PADDING)) {
            throw new GatewayException('Nagad: encryption with the Nagad public key failed (check the key).', 'Nagad is not configured correctly.');
        }
        return base64_encode($out);
    }

    private function decrypt(string $b64): string
    {
        $key = openssl_pkey_get_private($this->pem($this->cfg->cred('merchant_private_key'), 'PRIVATE'));
        if (!$key || !openssl_private_decrypt((string) base64_decode($b64), $out, $key, OPENSSL_PKCS1_PADDING)) {
            throw new GatewayException('Nagad: decrypting the response with the merchant private key failed.', 'Nagad is not configured correctly.');
        }
        return $out;
    }

    private function sign(string $data): string
    {
        $key = openssl_pkey_get_private($this->pem($this->cfg->cred('merchant_private_key'), 'PRIVATE'));
        if (!$key || !openssl_sign($data, $sig, $key, OPENSSL_ALGO_SHA256)) {
            throw new GatewayException('Nagad: signing with the merchant private key failed.', 'Nagad is not configured correctly.');
        }
        return base64_encode($sig);
    }

    private function verifySignature(string $data, string $sigB64): bool
    {
        $key = openssl_pkey_get_public($this->pem($this->cfg->cred('nagad_public_key'), 'PUBLIC'));
        return $key && openssl_verify($data, (string) base64_decode($sigB64), $key, OPENSSL_ALGO_SHA256) === 1;
    }

    /** Accepts PEM or the bare base64 body Nagad usually hands out. */
    private function pem(string $key, string $type): string
    {
        $key = trim($key);
        if (strpos($key, '-----BEGIN') === 0) {
            return $key;
        }
        $body = chunk_split(preg_replace('/\s+/', '', $key), 64, "\n");
        $label = $type === 'PRIVATE' ? 'PRIVATE KEY' : 'PUBLIC KEY';
        return "-----BEGIN $label-----\n$body-----END $label-----\n";
    }

    private function errorText(array $res): string
    {
        $parts = array();
        foreach (array('reason', 'message', 'status') as $k) {
            if (!empty($res[$k])) {
                $parts[] = $res[$k];
            }
        }
        return $parts ? implode(' / ', $parts) : json_encode($res);
    }
}
