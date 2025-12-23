<?php
// FILE: app/Services/ProductService.php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    // Создание товара
    public function create(array $data)
    {
        // Извлекаем файлы, чтобы они не попали в fillable товара
        $images = $data['images'] ?? [];
        unset($data['images']);

        $product = Product::create($data);

        // Сохраняем изображения
        if (!empty($images)) {
            foreach ($images as $index => $file) {
                $path = $file->store('media', 'tenant');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'sort_order' => $index // Первое загруженное будет 0
                ]);
            }
        }

        return $product;
    }

    // Обновление товара
    public function update(Product $product, array $data)
    {
        // 1. Новые файлы
        if (!empty($data['new_images'])) {
            // Находим текущий максимальный sort_order, чтобы добавить новые в конец
            $maxOrder = $product->images()->max('sort_order') ?? -1;
            
            foreach ($data['new_images'] as $file) {
                $path = $file->store('media', 'tenant');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'sort_order' => ++$maxOrder
                ]);
            }
        }

        // 2. Удаление файлов (если переданы ID)
        if (!empty($data['deleted_images'])) {
            $imagesToDelete = ProductImage::whereIn('id', $data['deleted_images'])
                                          ->where('product_id', $product->id)
                                          ->get();
            
            foreach ($imagesToDelete as $img) {
                Storage::disk('tenant')->delete($img->path);
                $img->delete();
            }
        }

        // 3. Сортировка (передан массив ID в нужном порядке)
        if (!empty($data['sorted_images'])) {
            $orderMap = array_flip($data['sorted_images']); // [id => index]
            $images = $product->images()->get();
            
            foreach ($images as $img) {
                if (isset($orderMap[$img->id])) {
                    $img->update(['sort_order' => $orderMap[$img->id]]);
                }
            }
        }

        // Обновляем основные поля
        unset($data['new_images'], $data['deleted_images'], $data['sorted_images']);
        $product->update($data);

        return $product;
    }
    
    // Удаление товара (с файлами)
    public function delete(Product $product)
    {
        foreach ($product->images as $img) {
            Storage::disk('tenant')->delete($img->path);
        }
        $product->images()->delete();
        $product->delete();
    }
}