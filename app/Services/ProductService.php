<?php
// FILE: app/Services/ProductService.php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            // 1. Сохраняем в PostgreSQL
            $product = Product::create($data);

            // 2. Индексируем в Elastic (можно вынести в Job/Queue для скорости, но пока делаем синхронно)
            try {
                $this->searchService->indexProduct($product);
            } catch (\Exception $e) {
                // Логируем, но не ломаем создание товара, если Elastic недоступен
                // В продакшене здесь нужен механизм ретраев
                \Illuminate\Support\Facades\Log::error("Failed to index product {$product->id}: " . $e->getMessage());
            }

            return $product;
        });
    }
    
    public function search(string $query): \Illuminate\Database\Eloquent\Collection
    {
        // 1. Получаем ID из Elastic
        $ids = $this->searchService->search($query);

        if (empty($ids)) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        // 2. Загружаем модели из базы в правильном порядке
        // FIELD(id, ...) нужен чтобы сохранить релевантность сортировки Elastic
        $idsString = implode(',', $ids);
        return Product::whereIn('id', $ids)
            ->orderByRaw("array_position(ARRAY[{$idsString}], id)") // PostgreSQL синтаксис
            ->get();
    }
}