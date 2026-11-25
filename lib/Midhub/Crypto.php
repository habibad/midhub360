<?php
namespace Midhub;

/**
 * Authenticated encryption (AES-256-GCM) for gateway credentials and voucher PINs at rest.
 *
 * Key resolution, first match wins:
 *   1. APP_KEY in .env / environment ("base64:<44 chars>" or any string of 32+ chars)
 *   2. File at MIDHUB_KEY_FILE (env), or storage/keys/app.key (auto-generated on first use)
 *
 * A database dump alone therefore never reveals gateway secrets. Losing the key means
 * re-entering credentials in Admin > Gateway Settings, so back the key up with the server.
 */
final class Crypto
{
    const PREFIX = 'enc:v1:';

    /** @var string|null */
    private static $key = null;

    public static function key(): string
    {
        if (self::$key !== null) {
            return self::$key;
        }
        $appKey = function_exists('env') ? env('APP_KEY') : getenv('APP_KEY');
        if (is_string($appKey) && $appKey !== '') {
            if (strpos($appKey, 'base64:') === 0) {
                $raw = base64_decode(substr($appKey, 7), true);
                if ($raw !== false && strlen($raw) >= 32) {
                    return self::$key = substr($raw, 0, 32);
                }
            } elseif (strlen($appKey) >= 32) {
                return self::$key = hash('sha256', $appKey, true);
            }
        }

        $file = function_exists('env') ? env('MIDHUB_KEY_FILE') : getenv('MIDHUB_KEY_FILE');
        if (!is_string($file) || $file === '') {
            $file = MIDHUB_ROOT . '/storage/keys/app.key';
        }
        if (is_file($file)) {
            $raw = base64_decode(trim((string) file_get_contents($file)), true);
            if ($raw !== false && strlen($raw) === 32) {
                return self::$key = $raw;
            }
            throw new \RuntimeException('Encryption key file is corrupt: ' . $file);
        }

        $dir = dirname($file);
        if (!is_dir($dir) && !@mkdir($dir, 0700, true)) {
            throw new \RuntimeException('Cannot create key directory ' . $dir . '. Set APP_KEY in .env instead.');
        }
        $raw = random_bytes(32);
        if (@file_put_contents($file, base64_encode($raw), LOCK_EX) === false) {
            throw new \RuntimeException('Cannot write encryption key to ' . $file . '. Set APP_KEY in .env instead.');
        }
        @chmod($file, 0600);
        return self::$key = $raw;
    }

    public static function encrypt(string $plain): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $ct = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag, 'midhub', 16);
        if ($ct === false) {
            throw new \RuntimeException('Encryption failed');
        }
        return self::PREFIX . base64_encode($iv . $tag . $ct);
    }

    /** Decrypts values written by encrypt(); unprefixed legacy plaintext passes through unchanged. */
    public static function decrypt(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (strpos($value, self::PREFIX) !== 0) {
            return $value;
        }
        $bin = base64_decode(substr($value, strlen(self::PREFIX)), true);
        if ($bin === false || strlen($bin) < 29) {
            throw new \RuntimeException('Encrypted value is malformed');
        }
        $plain = openssl_decrypt(substr($bin, 28), 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, substr($bin, 0, 12), substr($bin, 12, 16), 'midhub');
        if ($plain === false) {
            throw new \RuntimeException('Decryption failed (wrong APP_KEY or tampered data)');
        }
        return $plain;
    }

    public static function encryptJson(array $data): string
    {
        return self::encrypt(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /** @return array<string,mixed> */
    public static function decryptJson(?string $value): array
    {
        $plain = self::decrypt($value);
        if ($plain === '') {
            return array();
        }
        $data = json_decode($plain, true);
        return is_array($data) ? $data : array();
    }

    /** "••••••3f9a" style mask for showing that a secret is set without revealing it. */
    public static function mask(string $secret): string
    {
        $len = strlen($secret);
        if ($len === 0) {
            return '';
        }
        if ($len <= 6) {
            return str_repeat('•', $len);
        }
        return str_repeat('•', min(10, $len - 4)) . substr($secret, -4);
    }
}
