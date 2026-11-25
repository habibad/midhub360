<?php
namespace Midhub\Gateway;

use Midhub\Recharge\PaywellService;
use Midhub\Voucher\PopularVoucherService;

/** Builds the service for a gateway slug from its live admin configuration. */
final class GatewayFactory
{
    public static function deposit(string $slug, ?GatewayConfig $config = null): DepositGatewayInterface
    {
        $config = $config ?: GatewayConfig::load($slug);
        switch ($slug) {
            case 'bkash':
                return new BkashTokenizedService($config);
            case 'nagad':
                return new NagadPaymentService($config);
            case 'upay':
                return new UpayPaymentService($config);
            case 'uddoktapay':
                return new UddoktaPayService($config);
            case 'stripe':
                return new StripeService($config);
        }
        throw new \InvalidArgumentException("'$slug' is not a deposit gateway");
    }

    /**
     * Any gateway's service, for the admin "Test connection" button.
     * @return object with testConnection(): array{ok:bool,message:string}
     */
    public static function any(string $slug, ?GatewayConfig $config = null)
    {
        $config = $config ?: GatewayConfig::load($slug);
        if ($slug === 'paywell') {
            return new PaywellService($config);
        }
        if ($slug === 'popular_voucher') {
            return new PopularVoucherService($config);
        }
        if ($slug === 'billpay') {
            return new \Midhub\BillPay\BillPayService($config);
        }
        if ($slug === 'sms') {
            return new \Midhub\Ops\SmsService($config);
        }
        return self::deposit($slug, $config);
    }
}
