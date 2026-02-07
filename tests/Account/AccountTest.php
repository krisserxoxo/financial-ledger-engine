<?php 

namespace Tests\Account;

use Ledger\Domain\Account\Account;
use Ledger\Domain\Account\Exceptions\InsufficientFundsException;
use Ledger\Domain\Account\Exceptions\AccountLockedException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AccountTest extends TestCase
{
    #[Test]
    public function can_deposit_and_get_balance(): void
    {
        $account = new Account('2026-01-01');

        $account->deposit(1000, '2026-01-01');
        $this->assertEquals(1000, $account->getBalanceAt('2026-01-01'));
    }

    #[Test]
    public function cannot_withdraw_more_than_balance(): void
    {
        $account = new Account('2026-01-01');
        $account->deposit(500, '2026-01-01');

        $this->expectException(InsufficientFundsException::class);
        $account->withdraw(600, '2026-01-02');
    }

    #[Test]
    public function cannot_withdraw_from_locked_account(): void
    {
        $account = new Account('2026-01-01', '2026-02-01');
        $account->deposit(500, '2026-01-01');

        $this->expectException(AccountLockedException::class);
        $account->withdraw(100, '2026-01-15');
    }
}