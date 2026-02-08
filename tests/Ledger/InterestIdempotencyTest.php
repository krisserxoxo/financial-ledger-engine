<?php

namespace Tests\Ledger;

use Ledger\Domain\Ledger\Ledger;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class InterestIdempotencyTest extends TestCase
{
    #[Test]
    public function running_interest_twice_does_not_duplicate_interest(): void
    {
        $ledger = new Ledger();

        $ledger->deposit(10000, '2026-01-01');

        $ledger->runMonthlyInterest('2026-01-31', 0.01);
        $balanceAfterFirstRun = $ledger->getBalanceAt('2026-01-31');

        // samme rente bliver tilføjet igen
        $ledger->runMonthlyInterest('2026-01-31', 0.01);
        $balanceAfterSecondRun = $ledger->getBalanceAt('2026-01-31');

        $this->assertEquals($balanceAfterFirstRun, $balanceAfterSecondRun);
    }
}