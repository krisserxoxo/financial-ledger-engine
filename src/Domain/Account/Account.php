<?php

namespace Ledger\Domain\Account;

use Ledger\Domain\Ledger\Ledger;
use Ledger\Domain\Account\Exceptions\InsufficientFundsException;
use Ledger\Domain\Account\Exceptions\AccountLockedException;
use Ledger\Domain\Audit\AuditLog;

// Account klasse - repræsenterer en bankkonto
class Account
{
    public string $createdAt;
    public ?string $lockedUntil;
    private Ledger $ledger;

    public function __construct(string $createdAt, ?string $lockedUntil = null, ?AuditLog $audit = null)
    {
        $this->createdAt = $createdAt;
        $this->lockedUntil = $lockedUntil;
        $this->ledger = new Ledger($audit);

        $this->ledger->getAuditLog()->add('account_created', [
            'created_at' => $createdAt,
            'locked_until' => $lockedUntil
        ]);
    }

    public function getAuditLog(): AuditLog
    {
        return $this->ledger->getAuditLog();
    }

    // Foretager en indbetaling
    public function deposit(float $amount, string $date): void
    {
        $this->ledger->deposit($amount, $date);
    }

    //Foretager en udbetaling såfremt kontoen ikke er låst og saldoen er tilstrækkelig
    public function withdraw(float $amount, string $date): void
    {
        if ($this->lockedUntil !== null && $date < $this->lockedUntil) {
            throw new AccountLockedException();
        }

        if ($this->ledger->getBalanceAt($date) < $amount) {
            throw new InsufficientFundsException();
        }

        $this->ledger->withdraw($amount, $date);
    }

    // Henter balancen på en given dato
    public function getBalanceAt(string $date): float
    {
        return $this->ledger->getBalanceAt($date);
    }

    // Beregner og registerer månedlig rente
    public function runMonthlyInterest(string $date, float $rate): void
    {
        $this->ledger->runMonthlyInterest($date, $rate);
    }

    // Retter en transaktion ved at angive dens nye beløb
    public function correctTransactionById(string $transactionId, float $newAmount): void
    {
        $this->ledger->correctTransactionById($transactionId, $newAmount);
    }

    // Henter en transaktion ved dens ID
    public function getTransactionById(string $transactionId): ?\Ledger\Domain\Ledger\Transaction
    {
        return $this->ledger->getTransactionById($transactionId);
    }

    // Henter alle transaktioner
    public function getAllTransactions(): array
    {
        return $this->ledger->getAllTransactions();
    }

    public function balance(?string $date = null): float
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        return $this->ledger->getBalanceAt($date);
    }

    // Henter antallet af transaktioner
    public function transactionCount(): int
    {
        return $this->ledger->transactionCount();
    }
}