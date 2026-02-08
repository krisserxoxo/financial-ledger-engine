<?php

namespace Tests\Ledger;

use Ledger\Domain\Ledger\Ledger;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CorrectionTest extends TestCase
{
    #[Test]
    public function correction_creates_new_transaction_and_recalculates_interest(): void
    {
        $ledger = new Ledger();

        // Januar
        $ledger->deposit(1000, '2026-01-01');
        $ledger->runMonthlyInterest('2026-01-31', 0.01);

        // Februar
        $ledger->runMonthlyInterest('2026-02-28', 0.01);

        $balanceBefore = $ledger->getBalanceAt('2026-02-28');

        // find deposit transaction ID
        $transactions = $ledger->getAllTransactions();
        $depositTx = $transactions[0];
        $depositId = $depositTx->id;

        // Vi opdager at januar deposit skulle have været 800
        $ledger->correctTransactionById($depositId, 800);

        $balanceAfter = $ledger->getBalanceAt('2026-02-28');

        // saldo skal være lavere efter korrektion
        $this->assertLessThan($balanceBefore, $balanceAfter);

        // historik må ikke ændres - der må kun tilføjes
        $this->assertTrue($ledger->hasMoreTransactionsThan(3));
    }
}