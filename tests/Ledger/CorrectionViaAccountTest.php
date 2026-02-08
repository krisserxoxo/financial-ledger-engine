<?php

use Ledger\Domain\Account\Account;

it('correction via account keeps audit trail intact', function () {

    $account = new Account('2024-01-01');

    $account->deposit(1000, '2024-01-01');
    $account->withdraw(200, '2024-01-10');

    $account->runMonthlyInterest('2024-01-31', 0.01);
    $balanceBefore = $account->balance();

    // find transaktions ID for hævningen
    $transactions = $account->getAllTransactions();
    $withdrawalTx = null;
    foreach ($transactions as $tx) {
        if ($tx->date === '2024-01-10' && $tx->type === 'withdrawal') {
            $withdrawalTx = $tx;
            break;
        }
    }

    // hævningen rettes fra -200 til -400
    $account->correctTransactionById($withdrawalTx->id, -400);

    $balanceAfter = $account->balance();

    // saldo skal ændres
    expect($balanceAfter)->toBeLessThan($balanceBefore);

    // audit trail: historikken må kun vokse
    expect($account->transactionCount())->toBeGreaterThan(3);
});