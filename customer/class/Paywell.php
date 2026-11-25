<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';

/**
 * Legacy Paywell facade, kept for backward compatibility.
 *
 * All Paywell traffic now goes through lib/Midhub/Recharge/PaywellService.php, configured
 * in Admin > Gateway Settings (Sandbox/Live, encrypted credentials). The API key and
 * encryption key that used to be hardcoded here were removed. If they were live keys,
 * ask Paywell to rotate them, because they remain in git history.
 */
class Paywell
{
    /** @deprecated Customer recharges go through RechargeService::submit(). */
    public static function paywell_recharge($number, $amount, $type, $ref_id)
    {
        $svc = \Midhub\Recharge\PaywellService::active();
        if (!$svc) {
            return json_encode(array('status' => 'inactive', 'message' => 'Paywell gateway is not active'));
        }
        $operator = \Midhub\Recharge\RechargeService::operatorFor((string) $number);
        $r = $svc->topup((string) $number, (string) $amount, (string) $type, (string) $operator, (string) $ref_id);
        return json_encode(array('status' => $r['status_code'], 'outcome' => $r['outcome'], 'message' => $r['message'], 'trx_id' => $r['trx_id']));
    }

    public static function get_company($phone)
    {
        $op = \Midhub\Recharge\RechargeService::operatorFor((string) $phone);
        return $op === null ? '' : $op;
    }

    public static function apistatus($apiid, $apicompany, $apiservice)
    {
        try {
            return \Midhub\Gateway\GatewayConfig::load('paywell')->isActive() ? 1 : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
