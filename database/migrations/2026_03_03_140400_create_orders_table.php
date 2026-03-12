<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pedidos
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // #0001, #0002...
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Camarero
            $table->enum('type', ['dine_in', 'takeaway', 'delivery'])->default('dine_in');
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'paid', 'cancelled'])->default('pending');
            $table->integer('guests')->default(1);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // Líneas de pedido
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // Snapshot del nombre
            $table->decimal('unit_price', 8, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total', 8, 2);
            $table->text('notes')->nullable(); // Instrucciones especiales
            $table->enum('status', ['pending', 'sent', 'preparing', 'ready', 'delivered', 'cancelled'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();
        });


        // Pagos
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Cajero
            $table->decimal('amount', 10, 2);
            $table->string('method')->default('cash'); // cash, card, bizum, invoice
            $table->string('reference')->nullable(); // Referencia TPV
            $table->enum('status', ['pending', 'completed', 'refunded', 'failed'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_item_modifiers');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
