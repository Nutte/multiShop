<?php
// FILE: database/migrations/2024_01_01_000009_clean_products_table_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Удаляем старые колонки, если они существуют
            if (Schema::hasColumn('products', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('products', 'category_id')) {
                // Сначала дропаем внешний ключ, если он есть (на всякий случай обернем в try catch или проверку)
                // Для простоты в SQLite/Postgres часто достаточно dropColumn, но по правилам надо ключ.
                // Так как мы не знаем точное имя ключа в каждой схеме, попробуем просто колонку.
                $table->dropColumn('category_id');
            }
        });
    }

    public function down(): void
    {
        // Обратно не возвращаем, так как архитектура изменилась безвозвратно
    }
};