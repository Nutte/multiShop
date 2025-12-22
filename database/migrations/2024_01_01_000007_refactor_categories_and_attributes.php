<?php
// FILE: database/migrations/2024_01_01_000007_refactor_categories_and_attributes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Таблица связей Товары <-> Категории
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        });

        // 2. Справочник атрибутов (Размеры, Типы, Материалы)
        // Здесь будут храниться уникальные значения: "Hoodie", "XL", "Cotton"
        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'size', 'product_type', 'material'
            $table->string('value'); // 'XL', 'Hoodie'
            $table->string('slug')->nullable();
            $table->timestamps();
            
            // Уникальность значения в рамках типа (не может быть два размера XL)
            $table->unique(['type', 'value']);
        });

        // 3. Миграция данных (если были) и удаление старой колонки
        // (Упрощено: просто удаляем колонку, так как данные тестовые)
        if (Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('attribute_options');
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};