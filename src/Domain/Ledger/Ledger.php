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

    public function deposit(float $amount, string $date): void
    {
        $this->transactions[] = new Transaction(
            $date,
            $amount,
            Transaction::TYPE_DEPOSIT
        );
    }

    public function withdraw(float $amount, string $date): void
    {
        $this->transactions[] = new Transaction(
            $date,
            -abs($amount),
            Transaction::TYPE_WITHDRAWAL
        );
    }

    public function runMonthlyInterest(string $date, float $rate): void
    {
        $balance = $this->getBalanceAt($date);

        if ($balance <= 0) {
            return;
        }

        $interest = round($balance * $rate, 2);

        $this->transactions[] = new Transaction(
            $date,
            $interest,
            Transaction::TYPE_INTEREST
        );
    }

    public function removeInterestTransactionsFrom(string $date): void
    {
        $this->transactions = array_values(array_filter(
            $this->transactions,
            fn (Transaction $t) =>
                !($t->type === Transaction::TYPE_INTEREST && $t->date >=$date)
        ));
    }

    public function recalculateMonthlyInterestFrom(string $fromDate, float $rate): void
    {
        // 1. fjern fremtidige renter
        $this->removeInterestTransactionsFrom($fromDate);

        // 2. find sidste transaktionsdato
        $dates = array_map(fn ($t) => $t->date, $this->transactions);

        if (empty($dates)) {
            return;
        }

        sort($dates);
        $lastDate = end($dates);

        // 3. start fra månedsslut efter fromDate
        $current = new \DateTime($fromDate);
        $current->modify('last day of this month');

        $end = new \DateTime($lastDate);
        $end->modify('last day of this month');

        while ($current <= $end) {
            $this->runMonthlyInterest($current->format('Y-m-d'), $rate);
            $current->modify('last day of next month');
        }
    }
}