<?php
// FILE: app/Services/CartService.php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class CartService
{
    // 2 часа жизни корзины
    private const TTL = 7200;

    /**
     * Получает ключ корзины для указанного ID сессии или текущей.
     */
    private function getCartKey(?string $sessionId = null): string
    {
        $id = $sessionId ?? Session::getId();
        return "cart:{$id}";
    }

    public function add(int $productId, int $quantity = 1): void
    {
        $product = Product::findOrFail($productId);
        $key = $this->getCartKey();
        
        // Получаем текущую корзину
        $cart = $this->get();
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
            ];
        }

        Redis::setex($key, self::TTL, json_encode($cart));
    }

    public function get(): array
    {
        $key = $this->getCartKey();
        $data = Redis::get($key);
        return $data ? json_decode($data, true) : [];
    }

    public function clear(): void
    {
        Redis::del($this->getCartKey());
    }

    public function total(): float
    {
        $cart = $this->get();
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * NEW: Переносит корзину из старой сессии в новую.
     * Используется при логине, когда regenerate() меняет ID сессии.
     */
    public function migrateSessionCart(string $oldSessionId, string $newSessionId): void
    {
        $oldKey = $this->getCartKey($oldSessionId);
        $newKey = $this->getCartKey($newSessionId);

        if (Redis::exists($oldKey)) {
            // Переименовываем ключ (атомарная операция)
            // Если в новой сессии уже была корзина, она перезапишется (или можно сделать merge, но rename надежнее для начала)
            Redis::rename($oldKey, $newKey);
            // Обновляем TTL для нового ключа
            Redis::expire($newKey, self::TTL);
        }
    }
}