<?php
// FILE: database/migrations/2024_01_01_000010_create_product_images_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Создаем таблицу изображений
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->integer('sort_order')->default(0); // 0 = Главная (обложка)
            $table->timestamps();
        });

        // 2. Переносим данные из старой колонки (если она есть и не пустая)
        // Это сырой SQL, так как модели могут измениться
        if (Schema::hasColumn('products', 'image_path')) {
            $products = DB::table('products')->whereNotNull('image_path')->get();
            foreach ($products as $product) {
                if (!empty($product->image_path)) {
                    DB::table('product_images')->insert([
                        'product_id' => $product->id,
                        'path' => $product->image_path,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // 3. Удаляем старую колонку
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_path')->nullable();
        });
        Schema::dropIfExists('product_images');
    }
};