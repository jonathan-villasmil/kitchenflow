<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if ($this->indexExists('orders', 'orders_number_unique')) {
                $table->dropUnique('orders_number_unique');
            }

            if (!$this->indexExists('orders', 'orders_restaurant_id_number_unique')) {
                $table->unique(['restaurant_id', 'number']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if ($this->indexExists('orders', 'orders_restaurant_id_number_unique')) {
                $table->dropUnique(['restaurant_id', 'number']);
            }

            if (!$this->indexExists('orders', 'orders_number_unique')) {
                $table->unique('number');
            }
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === $name);
    }
};
