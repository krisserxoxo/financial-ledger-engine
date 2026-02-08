<?php

namespace Ledger\Domain\Ledger;

class Transaction
{
    public string $date; // yyyy-mm-dd format
    public float $amount;
    public string $type;
    public string $period; // yyyy-mm

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_INTEREST = 'interest';

    public function __construct(string $date, float $amount, string $type = self::TYPE_DEPOSIT, ?string $period = null)
    {
        $this->date = $date;
        $this->amount = $amount;
        $this->type = $type;
        $this->period = $period ?? '';
    }
}