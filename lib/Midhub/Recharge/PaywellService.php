<?php
namespace Midhub\Recharge;

use Midhub\Gateway\GatewayConfig;
use Midhub\Gateway\GatewayException;
use Midhub\Gateway\TokenCache;
use Midhub\Http\HttpClient;
use Midhub\Money;

/**
 * Paywell mobile recharge API.
 *
 * Auth (per Paywell's API spec, same scheme the legacy integration used):
 *   token  = POST {base}/Authentication/PaywellAuth/getToken   Basic base64(api_username:api_password)
 *   bearer = base64( security_token : api_key : HMAC-SHA256(json_body, encryption_key) )
 *
 * Outcome classification is deliberately conservative, because a wrong "failed" means we
 * refund a recharge that was actually delivered:
 *   SUCCESS  provider returned a success status code
 *   FAILED   the request never reached Paywell, or Paywell returned a structured rejection
 *   PENDING  timeout, 5xx, unparseable body or a pending code: we keep the money held and
 *            settle later via enquiry (callback or cron poller). Never auto-refunded.
 */
final class PaywellService
{
    const SUCCESS = 'SUCCESS';
    const PENDING = 'PENDING';
    const FAILED = 'FAILED';

    /** @var GatewayConfig */
    private $cfg;

    public function __construct(GatewayConfig $cfg)
    {
        $this->cfg = $cfg;
    }

    public static function active(): ?self
    {
        $cfg = GatewayConfig::load('paywell', true);
        return $cfg->isActive() ? new self($cfg) : null;
    }

    public function config(): GatewayConfig
    {
        return $this->cfg;
    }

    /** Maps our operator name (grameenphone, robi, ...) to Paywell's operator code. */
    public function operatorCode(string $operator): string
    {
        $key = strtolower($operator);
        $maps = array(
            json_decode($this->cfg->extra('operator_codes'), true),                     // admin-configured
            json_decode((string) $this->cfg->definition['extra']['operator_codes']['default'], true), // registry default
        );
        foreach ($maps as $map) {
            if (is_array($map) && isset($map[$key]) && $map[$key] !== '') {
                return (string) $map[$key];
            }
        }
        throw new GatewayException("Paywell: no operator code mapped for '$operator'", 'This operator is not supported right now.');
    }

    /**
     * @param string $conType 'prepaid' | 'postpaid'
     * @return array{outcome:string,status_code:string,message:string,trx_id:?string,raw:mixed}
     */
    public function topup(string $msisdn, string $amount, string $conType, string $operator, string $refId): array
    {
        try {
            $body = array(
                'username' => $this->cfg->cred('username'),
                'password' => $this->cfg->cred('password'),
                'ref_id' => $refId,
                'msisdn' => $msisdn,
                'amount' => (int) round((float) Money::parse($amount)),
                'con_type' => strtolower($conType) === 'postpaid' ? 'postpaid' : 'prepaid',
                'operator' => $this->operatorCode($operator),
            );
            $resp = $this->signedPost($this->cfg->extra('topup_path'), $body, 'topup', $refId, 60);
        } catch (GatewayException $e) {
            // config/token problems happen before the top-up request is sent
            return array('outcome' => self::FAILED, 'status_code' => 'local', 'message' => $e->getMessage(), 'trx_id' => null, 'raw' => null);
        }
        return $this->classify($resp);
    }

    /** Server-to-server status check for a previously submitted ref_id. */
    public function enquiry(string $refId): array
    {
        try {
            $resp = $this->signedPost($this->cfg->extra('enquiry_path'), array(
                'username' => $this->cfg->cred('username'),
                'password' => $this->cfg->cred('password'),
                'ref_id' => $refId,
            ), 'enquiry', $refId, 30);
        } catch (GatewayException $e) {
            return array('outcome' => self::PENDING, 'status_code' => 'local', 'message' => $e->getMessage(), 'trx_id' => null, 'raw' => null);
        }
        $r = $this->classify($resp);
        // An enquiry that cannot find the ref is not proof the top-up failed while it may still be queued.
        if ($r['outcome'] === self::FAILED && $resp->neverReachedServer()) {
            $r['outcome'] = self::PENDING;
        }
        return $r;
    }

    /** Paywell retailer balance (for low-balance alerts). */
    public function balance(): ?string
    {
        $resp = $this->signedPost($this->cfg->extra('balance_path'), array(
            'username' => $this->cfg->cred('username'),
            'password' => $this->cfg->cred('password'),
        ), 'balance', null, 20);
        $j = $resp->json();
        if (isset($j['balanceData']['balance'])) {
            return Money::fromPaisa(Money::toPaisa((string) $j['balanceData']['balance']));
        }
        if (isset($j['data']['balance'])) {
            return Money::fromPaisa(Money::toPaisa((string) $j['data']['balance']));
        }
        return null;
    }

    public function testConnection(): array
    {
        try {
            TokenCache::forget('paywell');
            $this->token();
            $bal = null;
            try {
                $bal = $this->balance();
            } catch (\Throwable $e) {
                // token ok, balance endpoint failed: still report the token success
            }
            return array('ok' => true, 'message' => 'Token issued (' . $this->cfg->mode . ').' . ($bal !== null ? " Retailer balance: $bal BDT." : ' Balance endpoint did not return a balance.'));
        } catch (GatewayException $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    // ------------------------------------------------------------------------

    /** @return array{outcome:string,status_code:string,message:string,trx_id:?string,raw:mixed} */
    public function classify(\Midhub\Http\HttpResponse $resp): array
    {
        if ($resp->neverReachedServer()) {
            return array('outcome' => self::FAILED, 'status_code' => 'curl_' . $resp->errno, 'message' => 'Provider unreachable: ' . $resp->error, 'trx_id' => null, 'raw' => null);
        }
        $j = $resp->json();
        if ($resp->errno !== 0 || $j === null || $resp->status >= 500) {
            return array('outcome' => self::PENDING, 'status_code' => $resp->errno ? 'curl_' . $resp->errno : 'http_' . $resp->status, 'message' => 'Awaiting provider confirmation', 'trx_id' => null, 'raw' => $j);
        }
        $data = isset($j['data']) && is_array($j['data']) ? $j['data'] : array();
        $code = (string) (isset($j['status']) ? $j['status'] : (isset($j['status_code']) ? $j['status_code'] : (isset($j['statusCode']) ? $j['statusCode'] : (isset($data['status']) ? $data['status'] : ''))));
        $msg = (string) (isset($j['message']) ? $j['message'] : (isset($data['message']) ? $data['message'] : (isset($j['msg']) ? $j['msg'] : '')));
        $trx = null;
        foreach (array('trx_id', 'trxId', 'transaction_id', 'txn_id') as $k) {
            if (!empty($data[$k])) { $trx = (string) $data[$k]; break; }
            if (!empty($j[$k])) { $trx = (string) $j[$k]; break; }
        }
        $success = array_map('trim', explode(',', $this->cfg->extra('success_codes')));
        $pending = array_map('trim', explode(',', $this->cfg->extra('pending_codes')));
        if ($code !== '' && in_array($code, $success, true)) {
            $outcome = self::SUCCESS;
        } elseif ($code === '' || in_array($code, $pending, true)) {
            $outcome = self::PENDING;
        } else {
            $outcome = self::FAILED;
        }
        return array('outcome' => $outcome, 'status_code' => $code, 'message' => substr($msg, 0, 240), 'trx_id' => $trx, 'raw' => $j);
    }

    private function signedPost(string $path, array $body, string $title, ?string $ref, int $timeout): \Midhub\Http\HttpResponse
    {
        $json = json_encode($body, JSON_UNESCAPED_SLASHES);
        $hash = hash_hmac('sha256', $json, $this->cfg->cred('encryption_key'));
        $bearer = base64_encode($this->token() . ':' . $this->cfg->cred('api_key') . ':' . $hash);
        $resp = HttpClient::request('POST', $this->cfg->url($path), array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $bearer,
        ), $json, array('timeout' => $timeout, 'log_type' => 'paywell', 'log_title' => $title, 'log_ref' => $ref));
        if ($resp->status === 401 || $resp->status === 403) {
            TokenCache::forget('paywell');
        }
        return $resp;
    }

    private function token(): string
    {
        $cached = TokenCache::get('paywell', $this->cfg->mode);
        if ($cached) {
            return $cached['token'];
        }
        $resp = HttpClient::request('POST', $this->cfg->url($this->cfg->extra('token_path')), array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->cfg->cred('api_username') . ':' . $this->cfg->cred('api_password')),
        ), '', array('timeout' => 20, 'log_type' => 'paywell', 'log_title' => 'get_token'));
        $j = $resp->json();
        $token = isset($j['token']['security_token']) ? (string) $j['token']['security_token'] : '';
        if ($token === '') {
            throw new GatewayException('Paywell token request failed: HTTP ' . $resp->status . ' ' . ($resp->error ?: substr($resp->body, 0, 200)));
        }
        // token_exp_time may be seconds-to-live or an absolute timestamp; cap either way.
        $ttl = isset($j['token']['token_exp_time']) ? (int) $j['token']['token_exp_time'] : 1800;
        if ($ttl > 1000000000) {
            $ttl -= time();
        }
        $ttl = min(3600, max(300, $ttl));
        TokenCache::put('paywell', $this->cfg->mode, $token, '', $ttl);
        return $token;
    }
}
