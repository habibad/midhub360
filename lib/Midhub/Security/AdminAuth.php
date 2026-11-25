<?php
namespace Midhub\Security;

use Midhub\Db;

/**
 * Super Admin session check + Self-PIN verification for sensitive admin actions.
 * The PIN lockout (5 wrong attempts / 15 min, stored in the DB) is shared with
 * PinVerifier, so it cannot be reset by starting a new session.
 */
final class AdminAuth
{
    /**
     * Returns the logged-in super admin row, or null.
     * @return array<string,mixed>|null
     */
    public static function current()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = isset($_SESSION['us_id']) ? (int) $_SESSION['us_id'] : 0;
        $name = isset($_SESSION['user_name']) ? (string) $_SESSION['user_name'] : '';
        if ($id <= 0 || $name === '') {
            return null;
        }
        $row = Db::one('SELECT id, user, level, active FROM user WHERE id = ? AND user = ? LIMIT 1', 'is', array($id, $name));
        if (!$row || (int) $row['level'] !== 5 || (string) $row['active'] !== '1') {
            return null;
        }
        return $row;
    }

    /** Stops with 403 unless a super admin is logged in. Returns the admin row. */
    public static function requireSuperAdmin(): array
    {
        $admin = self::current();
        if ($admin === null) {
            http_response_code(403);
            exit('Forbidden: Super Admin login required.');
        }
        return $admin;
    }

    /** @return array{ok:bool,error:string} */
    public static function verifyPin(int $adminId, string $pin): array
    {
        $r = PinVerifier::verify($adminId, $pin, 5);
        if (!$r['ok']) {
            $r['error'] = str_replace('Wrong PIN.', 'Wrong Self-PIN.', $r['error']);
        }
        return $r;
    }
}
