<?php

namespace Tests\Audit;

use Ledger\Domain\Audit\AuditLog;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AuditLogTest extends TestCase
{
    #[Test]
    public function it_records_audit_entries(): void
    {
        $audit = new AuditLog();

        $audit->add('deposit', ['amount' => 100, 'date' => '2026-01-01']);
        $audit->add('withdraw', ['amount' => 50, 'date' => '2026-01-02']);

        $entries = $audit->all();

        $this->assertCount(2, $entries);
        $this->assertEquals('deposit', $entries[0]['action']);
        $this->assertEquals(100, $entries[0]['data']['amount']);
        $this->assertEquals('withdraw', $entries[1]['action']);
        $this->assertEquals(50, $entries[1]['data']['amount']);
    }

    #[Test]
    public function count_returns_correct_number_of_entries(): void
    {
        $audit = new AuditLog();
        $this->assertEquals(0, $audit->count());

        $audit->add('deposit', ['amount' => 100]);
        $this->assertEquals(1, $audit->count());
    }

}