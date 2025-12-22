
<?php
// FILE: database/migrations/2024_01_01_000001_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Уникален в рамках одной схемы
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('category'); // Например: 'shoes', 'jackets'
            $table->string('sku')->nullable(); // Артикул
            $table->integer('stock_quantity')->default(0);
            $table->json('attributes')->nullable(); // Размер, цвет и т.д.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};  