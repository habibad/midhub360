<?php
namespace Midhub\Gateway;

/**
 * Contract for add-money gateways.
 *
 * Security model shared by all implementations:
 *  - Browser redirects and webhooks are only TRIGGERS. A wallet is credited solely from a
 *    server-to-server verification (verify() / completeReturn()) whose amount, currency
 *    and invoice match what we stored.
 *  - DepositService calls completeReturn()/verify() while holding a row lock on the
 *    transaction, so duplicate callbacks are processed exactly once.
 *
 * $txn is the gateway_transactions row (gateway_state decoded into $txn['state']).
 */
interface DepositGatewayInterface
{
    /**
     * Opens a checkout session.
     * @param array<string,mixed> $txn
     * @param array<string,mixed> $customer id, user, fullname, phone, email
     * @return array{redirect_url:string,gateway_payment_id:?string,state:array}
     */
    public function createCheckout(array $txn, array $customer): array;

    /**
     * Handles the customer's return from the checkout page (capture + verify).
     * @param array<string,mixed> $txn
     * @param array<string,string> $params query/body parameters from the provider redirect
     */
    public function completeReturn(array $txn, array $params): PaymentResult;

    /** Pure status query, used by webhooks and the reconciliation cron. */
    public function verify(array $txn): PaymentResult;

    /** @return array{ok:bool,message:string} */
    public function testConnection(): array;
}
