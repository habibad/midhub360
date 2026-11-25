<?php
namespace Midhub\Gateway;

use Midhub\Crypto;
use Midhub\Db;

/**
 * Caches short-lived provider tokens (encrypted) so we don't re-authenticate on every
 * request. bKash rate-limits Grant Token calls; a cached id_token is valid for 1 hour.
 * Tokens are cached per mode, so switching Sandbox/Live never reuses the wrong token.
 */
final class TokenCache
{
    /** @return array{token:string,refresh:string}|null */
    public static function get(string $slug, string $mode)
    {
        $row = Db::one('SELECT token, refresh_token FROM gateway_tokens WHERE gateway_slug = ? AND mode = ? AND expires_at > (NOW() + INTERVAL 60 SECOND)', 'ss', array($slug, $mode));
        if (!$row) {
            return null;
        }
        try {
            return array('token' => Crypto::decrypt($row['token']), 'refresh' => Crypto::decrypt($row['refresh_token']));
        } catch (\Throwable $e) {
            return null; // key rotated or corrupt: just fetch a new token
        }
    }

    public static function put(string $slug, string $mode, string $token, string $refresh, int $ttlSeconds): void
    {
        $ttl = max(60, $ttlSeconds - 120); // refresh a little before the provider's expiry
        Db::exec(
            'INSERT INTO gateway_tokens (gateway_slug, mode, token, refresh_token, expires_at) VALUES (?, ?, ?, ?, NOW() + INTERVAL ? SECOND)
             ON DUPLICATE KEY UPDATE token = VALUES(token), refresh_token = VALUES(refresh_token), expires_at = VALUES(expires_at)',
            'ssssi', array($slug, $mode, Crypto::encrypt($token), Crypto::encrypt($refresh), $ttl)
        );
    }

    /** Called when credentials change so stale tokens are never reused. */
    public static function forget(string $slug): void
    {
        Db::exec('DELETE FROM gateway_tokens WHERE gateway_slug = ?', 's', array($slug));
    }
}
