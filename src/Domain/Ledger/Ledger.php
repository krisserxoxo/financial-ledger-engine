<?php

namespace Ledger\Domain\Ledger;

use Ledger\Domain\Audit\AuditLog;

class Ledger
{
    /** @var Transaction[] */
    private array $transactions = [];
    private AuditLog $audit;

    public function __construct(?AuditLog $audit = null)
    {
        $this->audit = $audit ?? new AuditLog();
    }

    public function getAuditLog(): AuditLog
    {
        return $this->audit;
    }

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

        $this->audit->add('deposit', [
            'date' => $date,
            'amount' => $amount,
        ]);
    }

    public function withdraw(float $amount, string $date): void
    {
        $this->transactions[] = new Transaction(
            $date,
            -abs($amount),
            Transaction::TYPE_WITHDRAWAL
        );

        $this->audit->add('withdraw', [
            'date' => $date,
            'amount' => $amount,
        ]);
    }

    public function runMonthlyInterest(string $date, float $rate): void
    {
        $period = (new \DateTime($date))->format('Y-m');

        // tjek om der allerede er interest for perioden
        foreach ($this->transactions as $tx) {
            if ($tx->type === Transaction::TYPE_INTEREST && $tx->period === $period) {
                return; // allerede oprettet
            }
        }

        $balance = $this->getBalanceAt($date);
        if ($balance <= 0) return;

        $interest = round($balance * $rate, 2);

        $this->transactions[] = new Transaction(
            $date,
            $interest,
            Transaction::TYPE_INTEREST,
            $period
        );

        $this->audit->add('interest', [
            'date' => $date,
            'amount' => $interest,
            'rate' => $rate,
            'period' => $period,
        ]);
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
        // 1. find sidste renteperiode før der slettes
        $lastInterestDate = null;

        foreach ($this->transactions as $t) {
            if ($t->type === Transaction::TYPE_INTEREST) {
                if ($lastInterestDate === null || $t->date > $lastInterestDate) {
                    $lastInterestDate = $t->date;
                }
            }
        }

        if ($lastInterestDate === null) {
            return;
        }
        
        // 2. fjern fremtidige renter
        $this->removeInterestTransactionsFrom($fromDate);

        // 3. genberegn samme perioder igen
        $current = new \DateTime($fromDate);
        $current->modify('last day of this month');

        $end = new \DateTime($lastInterestDate);

        while ($current <= $end) {
            $this->runMonthlyInterest($current->format('Y-m-d'), $rate);
            $current->modify('last day of next month');
        }
    }

    public function correctTransaction(string $date, float $oldAmount, float $newAmount, float $rate = 0.01): void
    {
        $original = null;

        foreach ($this->transactions as $transaction) {
            if ($transaction->date === $date && $transaction->amount === $oldAmount) {
                $original = $transaction;
                break;
            }
        }

        if (!$original) {
            throw new \Exception("Transaction not found");
        }

        // difference bliver en ny immutable transaction
         $difference = $newAmount - $oldAmount;

         if ($difference != 0) {
            $this->transactions[] = new Transaction(
                $date,
                $difference,
                Transaction::TYPE_CORRECTION
            );

            $this->audit->add('correction', [
                'date' => $date,
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
                'difference' => $difference,
            ]);
         }

         // historien ændrer sig = renter skal genberegnes
         $this->recalculateMonthlyInterestFrom($date, $rate);
    }

    public function correctDeposit(string $date, float $oldAmount, float $newAmount): void
    {
        $difference = $newAmount - $oldAmount;

        // modpost (immutability, der ændres ikke på originalen)
        $this->transactions[] = new Transaction(
            $date,
            $difference,
            Transaction::TYPE_DEPOSIT
        );

        // renter efter denne dato skal genberegnes
        $this->recalculateMonthlyInterestFrom($date, 0.01);
    }

    public function hasMoreTransactionsThan(int $count): bool
    {
        return count($this->transactions) > $count;
    }

    public function transactionCount(): int
    {
        return count($this->transactions);
    }
}