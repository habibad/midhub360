<?php
namespace Midhub\Http;

final class HttpResponse
{
    /** @var int HTTP status (0 when no response was received) */
    public $status = 0;
    /** @var string */
    public $body = '';
    /** @var array<string,string> lower-cased response headers */
    public $headers = array();
    /** @var int curl errno (0 = ok) */
    public $errno = 0;
    /** @var string */
    public $error = '';
    /** @var int */
    public $durationMs = 0;

    /** @return array<string,mixed>|null decoded JSON object, null when the body is not JSON */
    public function json()
    {
        if ($this->body === '') {
            return null;
        }
        $d = json_decode($this->body, true);
        return is_array($d) ? $d : null;
    }

    public function ok(): bool
    {
        return $this->errno === 0 && $this->status >= 200 && $this->status < 300;
    }

    /**
     * True when the request certainly never reached the provider (DNS failure, refused
     * connection, TLS handshake failure). Only then is it safe to treat a money-moving
     * call as "not executed" and refund automatically. A timeout is NOT in this list:
     * the provider may have processed the request, so it must be verified by enquiry.
     */
    public function neverReachedServer(): bool
    {
        // 6 = COULDNT_RESOLVE_HOST, 7 = COULDNT_CONNECT, 35 = SSL_CONNECT_ERROR,
        // 51/60 = peer certificate verification failed, 5 = COULDNT_RESOLVE_PROXY
        return in_array($this->errno, array(5, 6, 7, 35, 51, 60), true);
    }

    public function timedOut(): bool
    {
        return $this->errno === 28; // CURLE_OPERATION_TIMEDOUT
    }
}
