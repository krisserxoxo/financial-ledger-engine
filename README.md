# Financial Ledger Engine

This is a hobby project implementing a **financial ledger engine** in PHP, designed as a backend domain model to manage accounts and transactions using an **immutable, history-based ledger**.

## Key Features Implemented

- Account creation with optional locked period
- Deposits and withdrawals with business rules:

  - Cannot withdraw more than available balance
  - Cannot withdraw from locked accounts
- Historical balance calculation at any date
- Immutable ledger: transactions are never changed
- Monthly interest calculation (compound interest)
- Automatic recalculation of future interest after historical changes
- Idempotent interest runs (safe re-processing of periods)

## Planned Features

- Retroactive corrections via correction transactions
- Audit logging of all events
- Integration with a persistence layer / database

## Purpose

This project demonstrates correct handling of historical balances, financial rules, and immutable ledger design — a core principle in banking and accounting systems.
It focuses on deterministic financial calculations: the same history always produces the same balance, even after retroactive corrections.

The project serves both as a learning exercise and as a showcase for backend domain modeling and financial consistency handling.
