<?php
namespace Midhub\Ops;

use Midhub\Gateway\GatewayConfig;
use Midhub\Gateway\GatewayException;
use Midhub\Http\HttpClient;

/**
 * Generic HTTP SMS sender for admin alerts. Bangladeshi bulk-SMS providers all expose a
 * "send" URL with the number, message and key as parameters, so the endpoint is an
 * admin-editable template: {to} {message} {api_key} {sender}.
 */
final class SmsService
{
    /** @var GatewayConfig */
    private $cfg;

    public function __construct(GatewayConfig $cfg)
    {
        $this->cfg = $cfg;
    }

    public static function active(): ?self
    {
        try {
            $cfg = GatewayConfig::load('sms', true);
            return $cfg->isActive() ? new self($cfg) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** @return string[] normalized admin numbers (01XXXXXXXXX) */
    public function adminPhones(): array
    {
        $out = array();
        foreach (preg_split('/[\s,;]+/', $this->cfg->extra('admin_phones')) as $p) {
            $p = preg_replace('/\D/', '', $p);
            if (strlen($p) === 13 && strpos($p, '880') === 0) {
                $p = substr($p, 2);
            }
            if (preg_match('/^01[3-9]\d{8}$/', $p)) {
                $out[] = $p;
            }
        }
        return array_values(array_unique($out));
    }

    public function wants(string $severity): bool
    {
        $min = strtolower($this->cfg->extra('min_severity')) === 'critical' ? 'critical' : 'warning';
        return $severity === 'critical' || ($severity === 'warning' && $min === 'warning');
    }

    public function send(string $to, string $message): bool
    {
        $message = substr(preg_replace('/\s+/', ' ', $message), 0, 300);
        $repl = array(
            '{to}' => rawurlencode($to),
            '{message}' => rawurlencode($message),
            '{api_key}' => rawurlencode($this->cfg->cred('api_key')),
            '{sender}' => rawurlencode($this->cfg->cred('sender_id', false)),
        );
        $path = strtr($this->cfg->extra('send_path'), $repl);
        $method = strtoupper($this->cfg->extra('method')) === 'POST' ? 'POST' : 'GET';
        $url = $this->cfg->url($path);
        $body = null;
        if ($method === 'POST' && strpos($url, '?') !== false) {
            list($url, $qs) = explode('?', $url, 2);
            $body = $qs;
        }
        $resp = HttpClient::request($method, $url, $method === 'POST' ? array('Content-Type' => 'application/x-www-form-urlencoded') : array(), $body,
            array('timeout' => 15, 'log_type' => 'sms', 'log_title' => 'send', 'log_ref' => $to, 'redact' => array('message')));
        $needle = $this->cfg->extra('success_contains');
        return $resp->ok() && ($needle === '' || stripos($resp->body, $needle) !== false);
    }

    public function testConnection(): array
    {
        $phones = $this->adminPhones();
        if (!$phones) {
            return array('ok' => false, 'message' => 'Add at least one admin phone number (01XXXXXXXXX) first.');
        }
        try {
            $ok = $this->send($phones[0], 'Midhub360 test alert: SMS gateway is working.');
        } catch (GatewayException $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
        return array('ok' => $ok, 'message' => $ok ? 'Test SMS sent to ' . $phones[0] . '.' : 'Provider did not confirm the SMS. Check the endpoint template, key and "success" text.');
    }
}
