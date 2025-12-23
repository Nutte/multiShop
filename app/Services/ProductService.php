<?php
// FILE: app/Services/ProductService.php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function create(array $data)
    {
        // Извлекаем варианты (размеры с количеством)
        $variantsData = $data['variants'] ?? [];
        $images = $data['images'] ?? [];
        
        unset($data['images'], $data['variants']);

        // Если переданы варианты, считаем общий сток
        if (!empty($variantsData)) {
            $data['stock_quantity'] = array_sum(array_column($variantsData, 'stock'));
        }

        $product = Product::create($data);

        // Сохраняем варианты
        if (!empty($variantsData)) {
            foreach ($variantsData as $variant) {
                if (!empty($variant['size'])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'size' => $variant['size'],
                        'stock' => (int)$variant['stock']
                    ]);
                }
            }
        }

        // Сохраняем изображения
        if (!empty($images)) {
            foreach ($images as $index => $file) {
                $path = $file->store('media', 'tenant');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'sort_order' => $index
                ]);
            }
        }

        return $product;
    }

    public function update(Product $product, array $data)
    {
        // 1. Обновление вариантов
        // Самый простой способ: удалить старые и создать новые (для простоты реализации)
        // В продакшене лучше делать updateOrCreate по ID
        if (isset($data['variants'])) {
            $product->variants()->delete(); // Удаляем старые
            $totalStock = 0;
            
            foreach ($data['variants'] as $variant) {
                if (!empty($variant['size'])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'size' => $variant['size'],
                        'stock' => (int)$variant['stock']
                    ]);
                    $totalStock += (int)$variant['stock'];
                }
            }
            $data['stock_quantity'] = $totalStock; // Обновляем общий сток
            
            // Обновляем JSON атрибутов тоже, чтобы поиск работал
            // Берем только имена размеров
            $sizeNames = array_map(fn($v) => $v['size'], $data['variants']);
            $currentAttributes = $product->attributes;
            $currentAttributes['size'] = $sizeNames;
            $data['attributes'] = $currentAttributes;
        }

        // 2. Изображения (Логика из прошлого шага)
        if (!empty($data['new_images'])) {
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

        if (!empty($data['deleted_images'])) {
            $imagesToDelete = ProductImage::whereIn('id', $data['deleted_images'])
                                          ->where('product_id', $product->id)->get();
            foreach ($imagesToDelete as $img) {
                Storage::disk('tenant')->delete($img->path);
                $img->delete();
            }
        }

        if (!empty($data['sorted_images'])) {
            $orderMap = array_flip($data['sorted_images']);
            foreach ($product->images()->get() as $img) {
                if (isset($orderMap[$img->id])) {
                    $img->update(['sort_order' => $orderMap[$img->id]]);
                }
            }
        }

        unset($data['new_images'], $data['deleted_images'], $data['sorted_images'], $data['variants']);
        
        $product->update($data);
        return $product;
    }

    public function delete(Product $product)
    {
        foreach ($product->images as $img) {
            Storage::disk('tenant')->delete($img->path);
        }
        $product->images()->delete();
        $product->variants()->delete(); // Удаляем варианты
        $product->delete();
    }
}