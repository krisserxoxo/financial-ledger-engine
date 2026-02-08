<?php

namespace Ledger\Domain\Ledger;

use Ledger\Domain\Audit\AuditLog;

// Ledger klasse - håndterer alle transaktioner for en konto
class Ledger
{
    /** @var Transaction[] */
    private array $transactions = [];
    private AuditLog $audit;

    // Ledger constructor
    public function __construct(?AuditLog $audit = null)
    {
        $this->audit = $audit ?? new AuditLog();
    }

    public function getAuditLog(): AuditLog
    {
        return $this->audit;
    }

    // Tilføj transaktion til ledger
    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    // Beregn balancen på en given dato
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

    // Registrer en indbetaling og tilføj til revisionslog
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

    // Registrer en udbetaling og tilføj til revisionslog
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

    // Beregn og registrer månedlig rente og sikre at rente kun beregnes én gang per måned
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

        // Beregn rente med 2 decimaler
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

    // Fjerner alle rentetransaktioner fra og med en given dato
    public function removeInterestTransactionsFrom(string $date): void
    {
        $this->transactions = array_values(array_filter(
            $this->transactions,
            fn (Transaction $t) =>
                !($t->type === Transaction::TYPE_INTEREST && $t->date >=$date)
        ));
    }

    // Genberegn månedlig rente fra en given dato
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

    // Finder en transaktion ved hjælp af dens ID
    public function getTransactionById(string $id): ?Transaction
    {
        foreach ($this->transactions as $tx) {
            if ($tx->id === $id) {
                return $tx;
            }
        }
        return null;
    }

    // Korreger transaktion ved at benytte ID i stedet for at matche med amount og dato (DEPRECATED VERSION)
    public function correctTransactionById(string $transactionId, float $newAmount, float $rate = 0.01): void
    {
        // Hent den originale transaktion
        $original = $this->getTransactionById($transactionId);

        if (!$original) {
            throw new \Exception("Transaction with ID '{$transactionId}' not found");
        }

        // Beregn difference mellem det gamle og nye beløb
        $oldAmount = $original->amount;
        $difference = $newAmount - $oldAmount;

        // Hvis forskel, oprettes korrektions-transaktion
        if ($difference != 0) {
            $correctionTx = new Transaction(
                $original->date,
                $difference,
                Transaction::TYPE_CORRECTION,
                null,
                $transactionId
            );

            $this->transactions[] = $correctionTx;

            $this->audit->add('correction', [
                'transaction_id' => $transactionId,
                'date' => $original->date,
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
                'difference' => $difference,
                'correction_id' => $correctionTx->id,
            ]);
        }

        // Genberegner renter fra originalens dato
        $this->recalculateMonthlyInterestFrom($original->date, $rate);

    }

    // Tjek om ledgeren har flere end et givet antal transaktioner - brugt til tests
    public function hasMoreTransactionsThan(int $count): bool
    {
        return count($this->transactions) > $count;
    }

    public function transactionCount(): int
    {
        return count($this->transactions);
    }

    public function getAllTransactions(): array
    {
        return $this->transactions;
    }
}