<?php
// FILE: app/Services/SearchService.php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchService
{
    protected string $host;
    protected string $port;
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
        $this->host = config('services.elasticsearch.host', 'elasticsearch');
        $this->port = config('services.elasticsearch.port', '9200');
    }

    /**
     * Получить имя индекса для текущего магазина (напр. street_style_products)
     */
    protected function getIndexName(): string
    {
        $tenantId = $this->tenantService->getCurrentTenantId();
        if (!$tenantId) {
            throw new \RuntimeException("Cannot determine index name: no tenant active.");
        }
        return "{$tenantId}_products";
    }

    protected function getUrl(string $endpoint): string
    {
        return "http://{$this->host}:{$this->port}/{$endpoint}";
    }

    /**
     * Создать индекс с маппингом (если не существует)
     */
    public function createIndexIfNotExists(): void
    {
        $index = $this->getIndexName();
        $url = $this->getUrl($index);

        // Проверяем существование (HEAD запрос)
        $exists = Http::head($url)->successful();

        if (!$exists) {
            $response = Http::put($url, [
                'mappings' => [
                    'properties' => [
                        'name' => ['type' => 'text'],
                        'description' => ['type' => 'text'],
                        'category' => ['type' => 'keyword'],
                        'price' => ['type' => 'float'],
                        'sku' => ['type' => 'keyword'],
                        'tenant_id' => ['type' => 'keyword'],
                    ]
                ]
            ]);
            
            Log::info("Created Elasticsearch index: {$index}", ['status' => $response->status()]);
        }
    }

    /**
     * Индексация товара
     */
    public function indexProduct(Product $product): bool
    {
        $this->createIndexIfNotExists(); // Ленивая инициализация

        $index = $this->getIndexName();
        $url = $this->getUrl("{$index}/_doc/{$product->id}");

        $response = Http::post($url, [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'category' => $product->category,
            'sku' => $product->sku,
            'created_at' => $product->created_at->toIso8601String(),
        ]);

        return $response->successful();
    }

    /**
     * Поиск товаров
     */
    public function search(string $query, int $limit = 20): array
    {
        $index = $this->getIndexName();
        $url = $this->getUrl("{$index}/_search");

        $body = [
            'size' => $limit,
            'query' => [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['name^3', 'description', 'sku'], // Name имеет приоритет x3
                    'fuzziness' => 'AUTO' // Обработка опечаток
                ]
            ]
        ];

        $response = Http::post($url, $body);

        if (!$response->successful()) {
            Log::error("Elasticsearch search failed", ['body' => $response->body()]);
            return [];
        }

        $hits = $response->json('hits.hits');
        
        // Возвращаем просто массив ID, чтобы контроллер загрузил модели из БД
        return array_map(fn($hit) => $hit['_id'], $hits);
    }
    
    /**
     * Удалить индекс (для полного сброса)
     */
    public function deleteIndex(): void
    {
        try {
            $index = $this->getIndexName();
            Http::delete($this->getUrl($index));
        } catch (\Exception $e) {
            // Игнорируем, если индекса нет или мы вне контекста
        }
    }
}