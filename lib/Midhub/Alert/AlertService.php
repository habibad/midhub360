<?php
namespace Midhub\Alert;

use Midhub\Db;

/**
 * Operational alerts for the Super Admin.
 * Writes to admin_alerts and to the existing `notification` table (shown in the admin
 * header bell), and optionally emails ALERT_EMAIL from .env. The same alert_key is not
 * repeated within $cooldownMinutes, so a cron job can call this every few minutes.
 */
final class AlertService
{
    public static function raise(string $key, string $severity, string $title, string $message, int $cooldownMinutes = 60): bool
    {
        $recent = (int) Db::scalar(
            'SELECT COUNT(*) FROM admin_alerts WHERE alert_key = ? AND created_at > (NOW() - INTERVAL ? MINUTE)',
            'si', array($key, $cooldownMinutes)
        );
        if ($recent > 0) {
            return false;
        }
        Db::insert('INSERT INTO admin_alerts (alert_key, severity, title, message) VALUES (?, ?, ?, ?)', 'ssss',
            array(substr($key, 0, 120), $severity, substr($title, 0, 150), $message));

        foreach (Db::all("SELECT id FROM user WHERE level = 5 AND active = '1'") as $admin) {
            Db::insert(
                "INSERT INTO notification (UserId, submitted_time, message, MsgFrom, seen, date) VALUES (?, NOW(), ?, ?, '0', CURDATE())",
                'iss', array((int) $admin['id'], substr($title . ': ' . $message, 0, 250) /* notification.message is VARCHAR(250); strict SQL mode rejects longer */, 'System Alert')
            );
        }

        // Admin phone alert (concept: merchant float alert to the admin's phone)
        try {
            $sms = \Midhub\Ops\SmsService::active();
            if ($sms && $sms->wants($severity)) {
                foreach ($sms->adminPhones() as $phone) {
                    $sms->send($phone, 'Midhub360 ' . strtoupper($severity) . ': ' . $title . '. ' . $message);
                }
            }
        } catch (\Throwable $e) {
            error_log('alert sms failed: ' . $e->getMessage());
        }

        $to = (string) env('ALERT_EMAIL', '');
        if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
            @mail($to, '[Midhub360 ' . strtoupper($severity) . '] ' . $title, $message, 'From: noreply@' . preg_replace('/^www\./', '', parse_url(APP_URL, PHP_URL_HOST) ?: 'localhost'));
        }
        return true;
    }
}
