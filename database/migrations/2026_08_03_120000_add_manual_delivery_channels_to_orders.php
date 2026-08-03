<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source')->default('pos')->after('type');
            $table->string('external_platform')->nullable()->after('source');
            $table->string('external_order_id')->nullable()->after('external_platform');
            $table->string('delivery_status')->nullable()->after('status');
        });

        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('address_line')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_deliveries');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'external_platform',
                'external_order_id',
                'delivery_status',
            ]);
        });
    }
};
