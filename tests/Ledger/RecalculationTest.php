<?php

namespace Tests\Ledger;

use Ledger\Domain\Ledger\Ledger;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RecalculationTest extends TestCase
{
    #[Test]
    public function recalculates_interest_when_past_transaction_changes(): void
    {
        $ledger = new Ledger();

        // januar
        $ledger->deposit(10000, '2026-01-01');
        $ledger->runMonthlyInterest('2026-01-31', 0.01);

        // februar
        $ledger->runMonthlyInterest('2026-02-28', 0.01);

        $originalBalance = $ledger->getBalanceAt('2026-02-28');

        // ny historisk transaktion i januar
        $ledger->deposit(5000, '2026-01-15');

        // genberegn
        $ledger->recalculateMonthlyInterestFrom('2026-01-15', 0.01);

        $newBalance = $ledger->getBalanceAt('2026-02-28');

        $this->assertGreaterThan($originalBalance, $newBalance);
    }
}