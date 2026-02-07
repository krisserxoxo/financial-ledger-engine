<?php

namespace Tests\Ledger;

use Ledger\Domain\Ledger\Ledger;
use Ledger\Domain\Ledger\Transaction;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HistoricBalanceTest extends TestCase
{
    #[TEST]
    public function it_calculates_historic_balance_correctly(): void
    {
        $ledger = new Ledger();

        // given
        $ledger->addTransaction(new Transaction('2026-01-01', 1000)); // indbetaling
        $ledger->addTransaction(new Transaction('2026-02-01', -200)); // hævning

        // assert
        $this->assertEquals(1000, $ledger->getBalanceAt('2026-01-15'));
        $this->assertEquals(800, $ledger->getBalanceAt('2026-02-15'));
    }
}