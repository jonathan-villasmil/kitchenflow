<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clockings', function (Blueprint $table) {
            $table->foreignId('shift_id')
                ->nullable()
                ->after('employee_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clockings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_id');
        });
    }
};
