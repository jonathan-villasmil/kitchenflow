<?php

namespace Tests\Unit;

use App\Models\Table;
use Tests\TestCase;

class TableTest extends TestCase
{
    public function test_table_availability_check(): void
    {
        $table = new Table(['status' => 'available']);
        $this->assertTrue($table->isAvailable());

        $table->status = 'occupied';
        $this->assertFalse($table->isAvailable());

        $table->status = 'dirty';
        $this->assertFalse($table->isAvailable());
    }
}
