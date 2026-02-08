# Financial Ledger Engine

This is a hobby project implementing a **financial ledger engine** in PHP, designed as a backend domain model to manage accounts and transactions using an **immutable, history-based ledger**.

## Key Features Implemented

- **Account Management**
  - Account creation with optional locked period
  - Deposits and withdrawals with business rules enforcement
  - Account locking: cannot withdraw from locked accounts
  - Overdraft prevention: cannot withdraw more than available balance

- **Immutable Transaction History**
  - Transactions are never modified, only appended
  - Unique transaction IDs for reliable tracking
  - Transaction types: deposits, withdrawals, interest, corrections
  - Complete audit trail of all operations

- **Historical Balance Queries**
  - Calculate account balance at any point in time
  - Deterministic: same history always produces the same balance
  - Supports retroactive corrections without losing history

- **Interest Calculation**
  - Monthly compound interest calculation
  - Idempotent interest runs (safe to re-process periods)
  - Automatic recalculation of future interest after corrections
  - Prevents duplicate interest in the same period

- **Retroactive Corrections**
  - Correct historical transactions using transaction IDs
  - Maintains immutability: corrections are new transactions
  - Automatically recalculates affected future interest
  - Full audit trail of what was corrected and when

- **Audit Logging**
  - Records all operations (deposits, withdrawals, interest, corrections)
  - Timestamps and detailed operation metadata
  - Accessible audit trail for compliance and debugging

- **TDD Test Coverage** with Pest
  - Account operations (`AccountTest`)
  - Ledger history and balance queries (`HistoricBalanceTest`)
  - Interest calculation and idempotency (`InterestTest`, `InterestIdempotencyTest`)
  - Retroactive corrections (`CorrectionTest`, `CorrectionViaAccountTest`)
  - Interest recalculation after corrections (`RecalculationTest`)

## Planned Features

- Integration with a persistence layer / database
- API endpoints for account operations
- Statement generation and reporting

## Purpose

This project demonstrates correct handling of historical balances, financial rules, and immutable ledger design — core principles in banking and accounting systems.

It focuses on **deterministic financial calculations**: the same transaction history always produces the same balance, regardless of when corrections are applied.

The project serves both as a learning exercise and as a showcase for backend domain modeling, financial consistency, and immutable data structures.