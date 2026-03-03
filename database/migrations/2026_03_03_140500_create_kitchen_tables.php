<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Estaciones de cocina
        Schema::create('kitchen_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Cocina caliente, Cocina fría, Barra
            $table->enum('type', ['hot', 'cold', 'bar', 'bakery', 'general'])->default('general');
            $table->string('color', 7)->default('#FF6B35');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Cola de comandas en cocina (KDS)
        Schema::create('kitchen_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kitchen_station_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'preparing', 'ready', 'delivered'])->default('pending');
            $table->integer('priority')->default(0); // Para reordenar en KDS
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_tickets');
        Schema::dropIfExists('kitchen_stations');
    }
};
