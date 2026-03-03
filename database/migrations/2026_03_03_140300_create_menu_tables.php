<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categorías del menú
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Platos / Productos
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('cost', 8, 2)->nullable(); // Costo real para calcular margen
            $table->string('image')->nullable();
            $table->string('sku')->nullable();
            $table->json('allergens')->nullable(); // gluten, lacteos, nueces, etc.
            $table->json('tags')->nullable(); // vegetariano, vegano, sin gluten, etc.
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('preparation_time_minutes')->nullable();
            $table->enum('kitchen_station', ['hot', 'cold', 'bar', 'bakery'])->default('hot');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['restaurant_id', 'slug']);
        });

        // Modificadores de platos (extras, opciones)
        Schema::create('dish_modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Ej: "Punto de cocción", "Extras"
            $table->boolean('required')->default(false);
            $table->boolean('multiple')->default(false); // Selección única o múltiple
            $table->integer('min_select')->default(0);
            $table->integer('max_select')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('dish_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_modifier_group_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Ej: "Sin cebolla", "Extra queso"
            $table->decimal('price_adjustment', 8, 2)->default(0); // 0 si no tiene coste
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Menús del día
        Schema::create('daily_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Menú del día, Menú degustación, etc.
            $table->decimal('price', 8, 2);
            $table->date('available_from')->nullable();
            $table->date('available_until')->nullable();
            $table->json('days_of_week')->nullable(); // [1,2,3,4,5] = lunes a viernes
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('daily_menu_dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->string('course'); // primero, segundo, postre, bebida
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_menu_dishes');
        Schema::dropIfExists('daily_menus');
        Schema::dropIfExists('dish_modifiers');
        Schema::dropIfExists('dish_modifier_groups');
        Schema::dropIfExists('dishes');
        Schema::dropIfExists('menu_categories');
    }
};
