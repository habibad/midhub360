<?php
namespace Midhub\Security;

/**
 * CSRF protection.
 *  - token()/field()/verify(): synchronizer tokens for new forms.
 *  - assertSameOrigin(): Origin/Referer verification (OWASP "verify origin with standard
 *    headers") for the legacy route.php endpoints, which have dozens of forms and GET
 *    action links without tokens. Those endpoints are never typed into the address bar,
 *    so a missing or foreign Origin/Referer means a forged cross-site request.
 */
final class Csrf
{
    const SESSION_KEY = '_midhub_csrf';

    public static function token(): string
    {
        self::ensureSession();
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verify($token): bool
    {
        self::ensureSession();
        $expected = isset($_SESSION[self::SESSION_KEY]) ? $_SESSION[self::SESSION_KEY] : '';
        return is_string($token) && $expected !== '' && hash_equals($expected, $token);
    }

    /** Verifies the POSTed _csrf field; responds 419 and stops otherwise. */
    public static function requireValidPost(): void
    {
        $token = isset($_POST['_csrf']) ? $_POST['_csrf'] : '';
        if (!self::verify($token)) {
            http_response_code(403); // not 419: Apache rewrites non-standard codes to 500
            exit('Security token expired or invalid. Go back, refresh the page and try again.');
        }
    }

    /** True when the request's Origin or Referer belongs to this host. */
    public static function isSameOrigin(): bool
    {
        $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
        if ($host === '') {
            return false;
        }
        $source = '';
        if (!empty($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] !== 'null') {
            $source = $_SERVER['HTTP_ORIGIN'];
        } elseif (!empty($_SERVER['HTTP_REFERER'])) {
            $source = $_SERVER['HTTP_REFERER'];
        }
        if ($source === '') {
            return false;
        }
        $srcHost = strtolower((string) parse_url($source, PHP_URL_HOST));
        $srcPort = parse_url($source, PHP_URL_PORT);
        if ($srcPort) {
            $srcHost .= ':' . $srcPort;
        }
        $bare = preg_replace('/:\d+$/', '', $host);
        return $srcHost === $host || $srcHost === $bare;
    }

    /** For legacy action endpoints: block cross-site (forged) requests. */
    public static function assertSameOrigin(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }
        if (!self::isSameOrigin()) {
            http_response_code(403);
            exit('Request blocked: this action must be submitted from the application itself. Go back, refresh and try again.');
        }
    }

    private static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
