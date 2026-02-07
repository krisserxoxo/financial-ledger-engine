<?php

namespace Ledger\Domain\Ledger;

class Ledger
{
    /** @var Transaction[] */
    private array $transactions = [];

    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    public function getBalanceAt(string $date): float
    {
        $balance = 0;

        foreach ($this->transactions as $tx) {
            if ($tx->date <= $date) {
                $balance += $tx->amount;
            }
        }

        return $balance;
    }
}