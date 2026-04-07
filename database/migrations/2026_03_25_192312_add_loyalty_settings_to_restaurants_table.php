<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('loyalty_points_per_unit', 8, 2)->default(1.00)->after('currency'); // 1 point per 1€
            $table->integer('loyalty_redemption_rate')->default(100)->after('loyalty_points_per_unit'); // 100 points = 1€
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points_per_unit', 'loyalty_redemption_rate']);
        });
    }
};
