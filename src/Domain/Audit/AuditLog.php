<?php

namespace Ledger\Domain\Audit;

class AuditLog
{
    private array $entries = [];

    public function add(string $action, array $data = []): void
    {
        $this->entries[] = [
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            'action' => $action,
            'data' => $data,
        ];
    }

    public function all(): array
    {
        return $this->entries;
    }

    public function count(): int
    {
        return count($this->entries);
    }
}