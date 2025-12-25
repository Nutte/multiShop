<?php
// FILE: database/migrations/2024_01_01_000016_create_orders_system.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FIX: Сначала удаляем таблицы, если они существуют.
        // Это предотвратит ошибку "relation already exists", так как таблица будет очищена перед созданием.
        // Важен порядок: сначала order_items (зависимая), потом orders (главная).
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        // 1. Таблица заказов (Сразу со всеми нужными полями)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // ORD-20250101-XXXX
            
            // Клиент
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone'); // Сразу добавляем телефон
            
            // Доставка и Оплата
            $table->string('shipping_method')->default('nova_poshta');
            $table->text('shipping_address'); // Сразу добавляем адрес
            $table->string('payment_method')->default('cod');
            $table->string('payment_status')->default('pending');
            
            // Финансы
            $table->decimal('subtotal', 10, 2); 
            $table->decimal('discount_amount', 10, 2)->default(0); 
            $table->decimal('total_amount', 10, 2); 
            $table->string('promo_code')->nullable(); 
            
            // Статус
            $table->string('status')->default('new'); // new, processing, shipped, completed, cancelled
            
            $table->timestamps();
        });

        // 2. Таблица товаров заказа
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Связь с товаром (nullable, чтобы история сохранялась при удалении товара)
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            
            // Снэпшот данных
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