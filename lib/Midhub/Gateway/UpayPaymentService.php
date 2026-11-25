<?php
namespace Midhub\Gateway;

use Midhub\Http\HttpClient;
use Midhub\Money;

/**
 * Upay merchant checkout.
 *
 * Upay does not publish its API. This follows the merchant flow Upay issues to
 * merchants (auth token -> payment init -> redirect -> single payment status), with
 * every endpoint path editable in Admin > Gateway Settings > Upay. Confirm the paths and
 * field names against your Upay integration document before going live.
 */
final class UpayPaymentService implements DepositGatewayInterface
{
    /** @var GatewayConfig */
    private $cfg;

    public function __construct(GatewayConfig $cfg)
    {
        $this->cfg = $cfg;
    }

    public function createCheckout(array $txn, array $customer): array
    {
        $body = array(
            'date' => date('Y-m-d'),
            'txn_id' => $txn['invoice_no'],
            'invoice_id' => $txn['invoice_no'],
            'amount' => Money::parse($txn['payable_amount']),
            'merchant_id' => $this->cfg->cred('merchant_id'),
            'merchant_name' => $this->cfg->cred('merchant_name'),
            'merchant_code' => $this->cfg->cred('merchant_code'),
            'merchant_country_code' => 'BD',
            'merchant_city' => $this->cfg->cred('merchant_city', false) ?: 'Dhaka',
            'merchant_category_code' => $this->cfg->cred('merchant_category_code', false),
            'merchant_mobile' => $this->cfg->cred('merchant_mobile'),
            'transaction_currency_code' => 'BDT',
            'redirect_url' => GatewayConfig::publicUrl('gateway/return.php?gw=upay&inv=' . rawurlencode($txn['invoice_no'])),
        );
        $res = $this->call('POST', $this->cfg->extra('init_path'), $body, 'payment_init', $txn['invoice_no']);
        $data = isset($res['data']) && is_array($res['data']) ? $res['data'] : $res;
        $url = isset($data['gateway_url']) ? $data['gateway_url'] : (isset($data['payment_url']) ? $data['payment_url'] : '');
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new GatewayException('Upay payment init failed: ' . $this->errorText($res), 'Upay is unavailable right now. Please try again later.');
        }
        $pid = isset($data['trx_id']) ? (string) $data['trx_id'] : (isset($data['session_id']) ? (string) $data['session_id'] : $txn['invoice_no']);
        return array('redirect_url' => (string) $url, 'gateway_payment_id' => $pid, 'state' => array());
    }

    public function completeReturn(array $txn, array $params): PaymentResult
    {
        // Upay's redirect parameters are informational only; settle by status query.
        return $this->verify($txn);
    }

    public function verify(array $txn): PaymentResult
    {
        $path = str_replace('{txn_id}', rawurlencode($txn['invoice_no']), $this->cfg->extra('status_path'));
        $res = $this->call('GET', $path, null, 'status', $txn['invoice_no']);
        $data = isset($res['data']) && is_array($res['data']) ? $res['data'] : $res;
        $status = strtolower((string) (isset($data['status']) ? $data['status'] : ''));
        $r = PaymentResult::make(PaymentResult::PENDING, $status, $res);
        $r->paidAmount = isset($data['amount']) ? (string) $data['amount'] : null;
        $r->currency = 'BDT';
        $r->gatewayTrxId = isset($data['trx_id']) ? (string) $data['trx_id'] : (isset($data['transaction_id']) ? (string) $data['transaction_id'] : null);
        $r->invoiceNo = isset($data['txn_id']) ? (string) $data['txn_id'] : (isset($data['invoice_id']) ? (string) $data['invoice_id'] : null);
        if (in_array($status, array('success', 'successful', 'completed', 'paid'), true)) {
            $r->status = PaymentResult::COMPLETED;
        } elseif (in_array($status, array('cancelled', 'canceled'), true)) {
            $r->status = PaymentResult::CANCELLED;
        } elseif (in_array($status, array('failed', 'failure', 'declined', 'expired'), true)) {
            $r->status = PaymentResult::FAILED;
        }
        return $r;
    }

    public function testConnection(): array
    {
        try {
            TokenCache::forget('upay');
            $this->token();
            return array('ok' => true, 'message' => 'Merchant authentication succeeded (' . $this->cfg->mode . ').');
        } catch (GatewayException $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    /** @return array<string,mixed> */
    private function call(string $method, string $path, ?array $body, string $title, string $ref): array
    {
        $resp = HttpClient::request($method, $this->cfg->url($path), array(
            'Accept' => 'application/json',
            'Authorization' => 'UPAY ' . $this->token(),
        ), $body, array('json' => true, 'timeout' => 30, 'log_type' => 'upay', 'log_title' => $title, 'log_ref' => $ref));
        if ($resp->status === 401) {
            TokenCache::forget('upay');
        }
        $json = $resp->json();
        if ($json === null) {
            throw new GatewayException("Upay $title transport error: HTTP {$resp->status} {$resp->error}");
        }
        return $json;
    }

    private function token(): string
    {
        $cached = TokenCache::get('upay', $this->cfg->mode);
        if ($cached) {
            return $cached['token'];
        }
        $resp = HttpClient::request('POST', $this->cfg->url($this->cfg->extra('auth_path')), array('Accept' => 'application/json'), array(
            'merchant_id' => $this->cfg->cred('merchant_id'),
            'merchant_key' => $this->cfg->cred('merchant_key'),
        ), array('json' => true, 'timeout' => 20, 'log_type' => 'upay', 'log_title' => 'auth'));
        $json = $resp->json();
        $token = '';
        if (is_array($json)) {
            $token = isset($json['data']['token']) ? (string) $json['data']['token'] : (isset($json['token']) ? (string) $json['token'] : '');
        }
        if ($token === '') {
            throw new GatewayException('Upay authentication failed: ' . ($json ? $this->errorText($json) : "HTTP {$resp->status} {$resp->error}"));
        }
        TokenCache::put('upay', $this->cfg->mode, $token, '', 3000);
        return $token;
    }

    private function errorText(array $res): string
    {
        $code = isset($res['code']) ? $res['code'] : '?';
        $msg = isset($res['message']) ? $res['message'] : (isset($res['msg']) ? $res['msg'] : json_encode($res));
        return "[$code] $msg";
    }
}
