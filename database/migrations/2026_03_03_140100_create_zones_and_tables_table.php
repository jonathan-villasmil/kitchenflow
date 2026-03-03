<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Zones / Salas del restaurante
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Mesas
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number'); // Número o nombre de la mesa
            $table->integer('capacity')->default(4);
            $table->decimal('pos_x', 8, 2)->default(0); // Posición en el plano
            $table->decimal('pos_y', 8, 2)->default(0);
            $table->decimal('width', 8, 2)->default(80);
            $table->decimal('height', 8, 2)->default(80);
            $table->string('shape')->default('rectangle'); // rectangle, circle, square
            $table->enum('status', ['available', 'occupied', 'reserved', 'cleaning', 'inactive'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['restaurant_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
        Schema::dropIfExists('zones');
    }
};
