<?php
// FILE: database/migrations/2024_01_01_000020_recreate_orders_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ЖЕСТКОЕ УДАЛЕНИЕ СТАРЫХ ТАБЛИЦ
        // Мы удаляем их, чтобы пересоздать с нуля по правильному чертежу.
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        // 2. СОЗДАНИЕ ПРАВИЛЬНОЙ ТАБЛИЦЫ ORDERS
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            
            // Клиент
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            
            // Доставка и Оплата
            $table->string('shipping_method')->default('nova_poshta');
            $table->text('shipping_address');
            $table->string('payment_method')->default('cod');
            $table->string('payment_status')->default('pending');
            
            // Финансы
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('promo_code')->nullable();
            
            // Статус
            $table->string('status')->default('new');
            
            $table->timestamps();
            
            // Внимание: колонки 'items' здесь НЕТ, и ошибки больше не будет.
        });

        // 3. СОЗДАНИЕ ПРАВИЛЬНОЙ ТАБЛИЦЫ ORDER_ITEMS
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            
            $table->string('product_name');
            $table->string('sku');
            $table->string('size')->nullable();
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};