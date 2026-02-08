<?php

use Ledger\Domain\Account\Account;

it('correction via account keeps audit trail intact', function () {

    $account = new Account('2024-01-01');

    $account->deposit(1000, '2024-01-01');
    $account->withdraw(200, '2024-01-10');

    $account->runMonthlyInterest('2024-01-31', 0.01);
    $balanceBefore = $account->balance();

    // hævningen rettes til 400
    $account->correctTransaction('2024-01-10', -200, -400);

    $balanceAfter = $account->balance();

    // saldo skal ændres
    expect($balanceAfter)->toBeLessThan($balanceBefore);

    // audit trail: historikken må kun vokse
    expect($account->transactionCount())->toBeGreaterThan(3);
});