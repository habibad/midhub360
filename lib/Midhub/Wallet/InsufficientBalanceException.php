<?php
namespace Midhub\Wallet;

final class InsufficientBalanceException extends \RuntimeException
{
    /** @var string */ public $balance;
    /** @var string */ public $required;

    public function __construct(string $balance, string $required)
    {
        parent::__construct("Insufficient balance: has $balance, needs $required");
        $this->balance = $balance;
        $this->required = $required;
    }
}
