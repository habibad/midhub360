<?php
namespace Midhub\Gateway;

/** Gateways that push server-to-server notifications. */
interface WebhookCapableInterface
{
    /**
     * Authenticates the raw webhook (signature / shared secret) and extracts identifiers.
     * Must throw GatewayException when authentication fails.
     *
     * @param array<string,string> $headers lower-cased header names
     * @return array{event_id:string,event_type:string,invoice_no:?string,gateway_payment_id:?string}
     */
    public function parseWebhook(string $rawBody, array $headers): array;
}
