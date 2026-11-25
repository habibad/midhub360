<?php
namespace Midhub\Gateway;

/** Outcome of a server-to-server payment verification. */
final class PaymentResult
{
    const COMPLETED = 'completed';
    const PENDING = 'pending';
    const FAILED = 'failed';
    const CANCELLED = 'cancelled';

    /** @var string */ public $status;
    /** @var string|null amount the provider says was paid, in the invoice currency */ public $paidAmount;
    /** @var string|null */ public $currency;
    /** @var string|null provider transaction id (bKash trxID, Stripe charge, ...) */ public $gatewayTrxId;
    /** @var string|null provider payment/session id */ public $gatewayPaymentId;
    /** @var string|null our invoice number as echoed back by the provider */ public $invoiceNo;
    /** @var string */ public $message = '';
    /** @var array<string,mixed> */ public $raw = array();

    public static function make(string $status, string $message = '', array $raw = array()): self
    {
        $r = new self();
        $r->status = $status;
        $r->message = $message;
        $r->raw = $raw;
        return $r;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::COMPLETED;
    }
}
