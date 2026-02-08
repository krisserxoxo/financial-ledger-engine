<?php

namespace Ledger\Domain\Account;

use Ledger\Domain\Ledger\Ledger;
use Ledger\Domain\Ledger\Transaction;
use Ledger\Domain\Account\Exceptions\InsufficientFundsException;
use Ledger\Domain\Account\Exceptions\AccountLockedException;

class Account
{
    public string $createdAt;
    public ?string $lockedUntil;
    private Ledger $ledger;

    public function __construct(string $createdAt, ?string $lockedUntil = null)
    {
        $this->createdAt = $createdAt;
        $this->lockedUntil = $lockedUntil;
        $this->ledger = new Ledger();
    }

    public function deposit(float $amount, string $date): void
    {
        $this->ledger->addTransaction(new Transaction($date, $amount));
    }

    public function withdraw(float $amount, string $date): void
    {
        if ($this->lockedUntil !== null && $date < $this->lockedUntil) {
            throw new AccountLockedException();
        }

        if ($this->ledger->getBalanceAt($date) < $amount) {
            throw new InsufficientFundsException();
        }

        $this->ledger->addTransaction(new Transaction($date, -$amount));
    }

    public function getBalanceAt(string $date): float
    {
        return $this->ledger->getBalanceAt($date);
    }

    public function runMonthlyInterest(string $date, float $rate): void
    {
        $this->ledger->runMonthlyInterest($date, $rate);
    }

    public function correctTransaction(string $date, float $oldAmount, float $newAmount): void
    {
        $this->ledger->correctTransaction($date, $oldAmount, $newAmount);
    }

    public function balance(?string $date = null): float
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        return $this->ledger->getBalanceAt($date);
    }

    public function transactionCount(): int
    {
        return $this->ledger->transactionCount();
    }
}