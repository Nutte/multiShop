<?php
// FILE: database/migrations/2024_01_01_000014_add_discounts_and_promocodes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Добавляем цену со скидкой в товары
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
        });

        // 2. Создаем таблицу промокодов
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Например: SUMMER2025
            $table->enum('type', ['percent', 'fixed']); // Тип скидки
            $table->decimal('value', 10, 2); // Значение (например 10% или 50$)
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sale_price');
        });
        Schema::dropIfExists('promo_codes');
    }
};