<?php

namespace Ledger\Domain\Ledger;

class Transaction
{
    public string $date; // yyyy-mm-dd format
    public float $amount;

    public function __construct(string $date, float $amount)
    {
        $this->date = $date;
        $this->amount = $amount;
    }
}