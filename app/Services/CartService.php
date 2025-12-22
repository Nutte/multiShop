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

    private function getCartKey(): string
    {
        // Используем ID сессии как идентификатор корзины пользователя
        $sessionId = Session::getId();
        return "cart:{$sessionId}";
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
}