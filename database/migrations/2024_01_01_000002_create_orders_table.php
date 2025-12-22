<?php
// FILE: database/migrations/2024_01_01_000002_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // ST-12345, DH-99999
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('new'); // new, paid, shipped
            $table->string('customer_email');
            $table->string('customer_name')->nullable();
            $table->json('items'); // Snapshot товаров на момент покупки
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};