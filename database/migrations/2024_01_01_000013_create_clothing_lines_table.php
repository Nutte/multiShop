<?php
// FILE: database/migrations/2024_01_01_000013_create_clothing_lines_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Таблица линеек
        Schema::create('clothing_lines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Уникальность внутри схемы тенанта
            $table->timestamps();
        });

        // 2. Связь в таблице товаров
        Schema::table('products', function (Blueprint $table) {
            // nullable - параметр не обязательный
            // nullOnDelete - если линейку удалят, товар останется (просто поле станет null)
            $table->foreignId('clothing_line_id')
                  ->nullable()
                  ->constrained('clothing_lines')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['clothing_line_id']);
            $table->dropColumn('clothing_line_id');
        });
        Schema::dropIfExists('clothing_lines');
    }
};