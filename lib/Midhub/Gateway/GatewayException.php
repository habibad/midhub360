<?php
namespace Midhub\Gateway;

/**
 * Raised for gateway failures. $userMessage is safe to show to customers;
 * getMessage() may contain technical detail for logs.
 */
class GatewayException extends \RuntimeException
{
    /** @var string */
    public $userMessage;

    public function __construct(string $message, string $userMessage = 'The payment provider is unavailable right now. Please try again later.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->userMessage = $userMessage;
    }
}
