<?php
// FILE: database/migrations/2024_01_01_000015_update_promo_codes_structure.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Мы пересоздадим таблицу, чтобы убедиться в правильности структуры
        Schema::dropIfExists('promo_codes');

        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Например: VIPCLIENT
            $table->enum('type', ['percent', 'fixed']); // Тип: % или $
            $table->decimal('value', 10, 2); // 10.00
            
            // Глобальные настройки
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // ЛОГИКА ПРИВЯЗКИ
            // 'global' = на всё везде
            // 'tenant' = на весь конкретный магазин
            // 'category' = на категорию (в конкретном магазине)
            // 'line' = на линейку (в конкретном магазине)
            // 'specific' = на конкретные товары
            $table->string('scope_type')->default('global'); 
            
            // JSON поле для хранения ID:
            // Пример: { "street_style": [1, 5, 10], "designer_hub": [2] }
            // Или для категорий: { "street_style": ["hoodies_slug"], "military": ["boots_slug"] }
            $table->json('scope_data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};