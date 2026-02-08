<?php

namespace Tests\Ledger;

use Ledger\Domain\Ledger\Ledger;
use Ledger\Domain\Ledger\Transaction;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class InterestTest extends TestCase
{
    #[Test]
    public function it_calculates_monthly_interest_correctly(): void
    {
        $ledger = new Ledger();

        // given
        $ledger->addTransaction(new Transaction('2026-01-01', 1000));

        // when: run interest for Jan, Feb
        $ledger->runMonthlyInterest('2026-01-31', 0.01); // 1% monthly
        $ledger->runMonthlyInterest('2026-02-28', 0.01);

        // then
        $this->assertEquals(1010, $ledger->getBalanceAt('2026-01-31')); // 1000 + 1%
        $this->assertEquals(1020.1, $ledger->getBalanceAt('2026-02-28')); // 1010 + 1%
    }
}