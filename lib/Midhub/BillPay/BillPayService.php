<?php
namespace Midhub\BillPay;

use Midhub\Gateway\GatewayConfig;
use Midhub\Gateway\GatewayException;
use Midhub\Http\HttpClient;
use Midhub\Http\HttpResponse;

/**
 * Utility bill aggregator client (shl.com.bd by default). Endpoints, token and the
 * Sandbox/Live {env} segment all come from Gateway Settings.
 *
 * Payment outcome classification is conservative, as for recharges: only an explicit
 * success marks a bill paid, only a structured rejection (or a request that never reached the
 * aggregator) refunds, and everything ambiguous stays "processing" for the status poller.
 */
final class BillPayService
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

    public static function load(): self
    {
        return new self(GatewayConfig::load('billpay', true));
    }

    public function config(): GatewayConfig
    {
        return $this->cfg;
    }

    /** api = pay instantly through the aggregator; manual = admin settles from the Bill Pay queue */
    public function apiMode(): bool
    {
        return strtolower($this->cfg->extra('payment_mode')) === 'api';
    }

    /**
     * @param array<string,string> $input number, contact, amount, month (e.g. "September 2025")
     * @return array decoded aggregator response (result, bill_ref, data, invoice)
     */
    public function fetch(string $company, string $type, array $input): array
    {
        $b = BillerCatalog::resolve($company, $type);
        if ($b === null) {
            throw new GatewayException("Unsupported biller $company", 'This biller is not supported yet.');
        }
        $body = array();
        foreach ($b['fields'] as $field => $src) {
            if ($src === 'month_m' || $src === 'month_y') {
                $d = \DateTime::createFromFormat('F Y', (string) $input['month']) ?: \DateTime::createFromFormat('M Y', (string) $input['month']);
                if (!$d) {
                    throw new GatewayException('Invalid bill month', 'Select the bill month.');
                }
                $body[$field] = $src === 'month_m' ? $d->format('m') : $d->format('Y');
            } else {
                $body[$field] = isset($input[$src]) ? (string) $input[$src] : '';
            }
        }
        $resp = $this->post($this->path('fetch_path', $this->slug($company, $b['slug'])), $body, 'fetch', null, 45);
        $j = $resp->json();
        if ($j === null) {
            throw new GatewayException("Bill fetch failed: HTTP {$resp->status} {$resp->error}", 'The biller could not be reached. Please try again.');
        }
        $j['_slug'] = $b['slug'];
        return $j;
    }

    /**
     * @param array<string,mixed> $bill billpay_request row
     * @return array{outcome:string,trx_id:?string,message:string,raw:mixed}
     */
    public function pay(array $bill, string $ref, string $slug): array
    {
        try {
            $resp = $this->post($this->path('pay_path', $slug), array(
                'bill_payment_id' => (string) $bill['bill_payment_id'],
                'bill_refer_id' => (string) $bill['bill_refer_id'],
                'bill_no' => (string) $bill['bill_no'],
                'amount' => (string) $bill['bill_total_amount'],
                'ref_id' => $ref,
            ), 'pay', $ref, 60);
        } catch (GatewayException $e) {
            return array('outcome' => self::FAILED, 'trx_id' => null, 'message' => $e->getMessage(), 'raw' => null); // never sent
        }
        return $this->classify($resp);
    }

    public function status(array $bill, string $ref, string $slug): array
    {
        try {
            $resp = $this->post($this->path('status_path', $slug), array(
                'bill_payment_id' => (string) $bill['bill_payment_id'],
                'ref_id' => $ref,
            ), 'status', $ref, 30);
        } catch (GatewayException $e) {
            return array('outcome' => self::PENDING, 'trx_id' => null, 'message' => $e->getMessage(), 'raw' => null);
        }
        $r = $this->classify($resp);
        return $r;
    }

    public function classify(HttpResponse $resp): array
    {
        if ($resp->neverReachedServer()) {
            return array('outcome' => self::FAILED, 'trx_id' => null, 'message' => 'Aggregator unreachable: ' . $resp->error, 'raw' => null);
        }
        $j = $resp->json();
        if ($resp->errno !== 0 || $j === null || $resp->status >= 500) {
            return array('outcome' => self::PENDING, 'trx_id' => null, 'message' => 'Awaiting aggregator confirmation', 'raw' => $j);
        }
        $d = isset($j['data']) && is_array($j['data']) ? $j['data'] : array();
        $result = strtolower((string) (isset($j['result']) ? $j['result'] : (isset($j['status']) ? $j['status'] : '')));
        $msg = substr((string) (isset($j['message']) ? $j['message'] : ''), 0, 240);
        $trx = null;
        foreach (array('transaction_id', 'trx_id', 'payment_id', 'txn_id') as $k) {
            if (!empty($d[$k])) { $trx = (string) $d[$k]; break; }
            if (!empty($j[$k])) { $trx = (string) $j[$k]; break; }
        }
        if (in_array($result, array('success', 'successful', 'paid', 'completed'), true) && (!isset($d['is_bill_paid']) || strtoupper((string) $d['is_bill_paid']) !== 'N')) {
            return array('outcome' => self::SUCCESS, 'trx_id' => $trx, 'message' => $msg, 'raw' => $j);
        }
        if (in_array($result, array('', 'pending', 'processing', 'accepted'), true)) {
            return array('outcome' => self::PENDING, 'trx_id' => $trx, 'message' => $msg, 'raw' => $j);
        }
        return array('outcome' => self::FAILED, 'trx_id' => $trx, 'message' => $msg ?: $result, 'raw' => $j);
    }

    public function testConnection(): array
    {
        try {
            $this->cfg->cred('token');
            $resp = HttpClient::request('GET', $this->cfg->url('/'), array(), null, array('timeout' => 10));
            if ($resp->neverReachedServer()) {
                return array('ok' => false, 'message' => 'Aggregator host not reachable: ' . $resp->error);
            }
            return array('ok' => true, 'message' => 'Aggregator reachable (' . $this->cfg->mode . ', payment mode: ' . ($this->apiMode() ? 'API' : 'MANUAL admin queue') . '). Fetch a real bill from the customer panel to verify the token.');
        } catch (GatewayException $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    // ------------------------------------------------------------------------

    private function slug(string $company, string $default): string
    {
        $over = json_decode($this->cfg->extra('biller_slugs'), true);
        return (is_array($over) && !empty($over[$company]) && preg_match('/^[a-z0-9-]{2,60}$/', $over[$company])) ? $over[$company] : $default;
    }

    private function path(string $key, string $slug): string
    {
        $env = $this->cfg->isLive() ? $this->cfg->extra('env_live') : $this->cfg->extra('env_sandbox');
        return strtr($this->cfg->extra($key), array('{env}' => rawurlencode($env), '{biller}' => rawurlencode($slug)));
    }

    private function post(string $path, array $body, string $title, ?string $ref, int $timeout): HttpResponse
    {
        return HttpClient::request('POST', $this->cfg->url($path), array(
            'Accept' => 'application/json',
            ($this->cfg->extra('token_header') ?: 'token') => $this->cfg->cred('token'),
        ), $body, array('multipart' => true, 'timeout' => $timeout, 'log_type' => 'billpay', 'log_title' => $title, 'log_ref' => $ref));
    }
}
