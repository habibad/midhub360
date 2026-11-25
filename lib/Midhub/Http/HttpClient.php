<?php
namespace Midhub\Http;

use Midhub\Db;

/**
 * cURL wrapper used by every gateway integration.
 *  - TLS peer verification is always on (the legacy code disabled it and forced TLSv1).
 *  - Every outgoing call is written to api_log with secrets redacted.
 *  - Explicit connect/total timeouts so a slow provider can't hang a PHP-FPM worker.
 */
final class HttpClient
{
    /** Keys whose values are never written to api_log. Matched case-insensitively as substrings. */
    const REDACT = array('password', 'secret', 'app_key', 'api_key', 'apikey', 'merchant_key', 'token',
        'authorization', 'x-app-key', 'username', 'sensitivedata', 'signature', 'pin', 'client_secret',
        'encryption_key', 'private', 'card', 'rt-uddoktapay-api-key', 'x-api-key', 'voucher_code', 'serial');

    /**
     * @param array<string,string> $headers name => value
     * @param string|array|null $body array => form-encoded unless $opts['json'] is true
     * @param array<string,mixed> $opts timeout, connect_timeout, json, log_type, log_title, log_ref, redact(array), log_response(bool)
     */
    public static function request(string $method, string $url, array $headers = array(), $body = null, array $opts = array()): HttpResponse
    {
        $timeout = isset($opts['timeout']) ? (int) $opts['timeout'] : 30;
        $connectTimeout = isset($opts['connect_timeout']) ? (int) $opts['connect_timeout'] : 10;
        $method = strtoupper($method);

        $payload = null;
        $logPayload = null;
        if ($body !== null) {
            if (is_array($body) && !empty($opts['multipart'])) {
                $payload = array_map('strval', $body); // curl sends multipart/form-data with its own boundary
                $logPayload = json_encode($body);
            } elseif (is_array($body)) {
                if (!empty($opts['json'])) {
                    $payload = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $headers += array('Content-Type' => 'application/json');
                } else {
                    $payload = http_build_query($body, '', '&');
                    $headers += array('Content-Type' => 'application/x-www-form-urlencoded');
                }
            } else {
                $payload = (string) $body;
            }
        }

        $headerLines = array();
        foreach ($headers as $k => $v) {
            $headerLines[] = $k . ': ' . $v;
        }

        $resp = new HttpResponse();
        $respHeaders = array();
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_USERAGENT => 'Midhub360-Gateway/1.0',
            CURLOPT_HEADERFUNCTION => function ($c, $line) use (&$respHeaders) {
                $p = strpos($line, ':');
                if ($p !== false) {
                    $respHeaders[strtolower(trim(substr($line, 0, $p)))] = trim(substr($line, $p + 1));
                }
                return strlen($line);
            },
        ));
        if ($payload !== null && $method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $start = microtime(true);
        $out = curl_exec($ch);
        $resp->durationMs = (int) round((microtime(true) - $start) * 1000);
        $resp->errno = curl_errno($ch);
        $resp->error = curl_error($ch);
        $resp->status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $resp->body = is_string($out) ? $out : '';
        $resp->headers = $respHeaders;
        curl_close($ch);

        if (!empty($opts['log_type'])) {
            $extra = isset($opts['redact']) ? (array) $opts['redact'] : array();
            self::log(
                (string) $opts['log_type'],
                isset($opts['log_title']) ? (string) $opts['log_title'] : $method,
                isset($opts['log_ref']) ? (string) $opts['log_ref'] : null,
                $url,
                array('method' => $method, 'headers' => $headers, 'body' => self::decodeForLog($logPayload !== null ? $logPayload : $payload)),
                array('status' => $resp->status, 'errno' => $resp->errno, 'error' => $resp->error,
                    'ms' => $resp->durationMs,
                    'body' => (isset($opts['log_response']) && $opts['log_response'] === false) ? '[omitted]' : self::decodeForLog($resp->body)),
                $extra
            );
        }
        return $resp;
    }

    /** Records an incoming webhook/callback (or any event) in api_log. */
    public static function log(string $type, string $title, ?string $ref, string $url, $request, $response, array $extraRedact = array()): void
    {
        try {
            $req = json_encode(self::redact($request, $extraRedact), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $res = json_encode(self::redact($response, $extraRedact), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            Db::insert(
                'INSERT INTO api_log (subscriptionRequestId, type, title, url, request, response) VALUES (?, ?, ?, ?, ?, ?)',
                'ssssss',
                array($ref === null ? null : substr($ref, 0, 50), substr($type, 0, 50), substr($title, 0, 50),
                    substr(self::stripQuerySecrets($url), 0, 255), substr((string) $req, 0, 65000), substr((string) $res, 0, 65000))
            );
        } catch (\Throwable $e) {
            error_log('api_log write failed: ' . $e->getMessage());
        }
    }

    /** @return mixed */
    public static function redact($data, array $extra = array())
    {
        $keys = array_merge(self::REDACT, array_map('strtolower', $extra));
        if (is_array($data)) {
            $out = array();
            foreach ($data as $k => $v) {
                $lk = strtolower((string) $k);
                $hit = false;
                foreach ($keys as $needle) {
                    if ($needle !== '' && strpos($lk, $needle) !== false) {
                        $hit = true;
                        break;
                    }
                }
                $out[$k] = $hit ? '***' : self::redact($v, $extra);
            }
            return $out;
        }
        return $data;
    }

    /** @return mixed */
    private static function decodeForLog($payload)
    {
        if ($payload === null || $payload === '') {
            return $payload;
        }
        $j = json_decode($payload, true);
        if (is_array($j)) {
            return $j;
        }
        parse_str($payload, $form);
        if (!empty($form) && strpos($payload, '=') !== false && strpos($payload, '<') === false) {
            return $form;
        }
        return strlen($payload) > 4000 ? substr($payload, 0, 4000) . '...[truncated]' : $payload;
    }

    private static function stripQuerySecrets(string $url): string
    {
        return preg_replace('/([?&](?:key|token|secret|password|api_key)=)[^&]*/i', '$1***', $url);
    }
}
