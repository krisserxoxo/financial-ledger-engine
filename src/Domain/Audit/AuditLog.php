<?php

namespace Ledger\Domain\Audit;

// Auditlog klasse - gemmer en historik over alle handlinger og ændringer angående konti
class AuditLog
{
    private array $entries = [];

    // Tiføj ny post til loggen, hver post indeholder timestamp, handling og relaterede data
    public function add(string $action, array $data = []): void
    {
        $this->entries[] = [
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            'action' => $action,
            'data' => $data,
        ];
    }

    // Retunerer alle log-entries
    public function all(): array
    {
        return $this->entries;
    }

    // Retunerer antallet af log-entries
    public function count(): int
    {
        return count($this->entries);
    }
}