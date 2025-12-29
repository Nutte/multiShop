<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Создание нового заказа
     */
    public function createOrder(array $validatedData): Order
    {
        return DB::transaction(function () use ($validatedData) {
            $total = $this->calculateOrderTotal($validatedData['items']);
            
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $validatedData['user_id'] ?? null,
                'customer_name' => $validatedData['customer_name'],
                'customer_phone' => $validatedData['customer_phone'],
                'customer_email' => $validatedData['customer_email'] ?? null,
                'shipping_method' => $validatedData['shipping_method'],
                'shipping_address' => $validatedData['shipping_address'],
                'status' => 'new',
                'subtotal' => $total,
                'total_amount' => $total,
                'is_instagram' => $validatedData['is_instagram'] ?? false,
            ]);

            $this->createOrderItems($order, $validatedData['items']);
            
            return $order;
        });
    }

    /**
     * Обновление существующего заказа
     */
    public function updateOrder(Order $order, array $validatedData, array $itemsData = null): Order
    {
        return DB::transaction(function () use ($order, $validatedData, $itemsData) {
            // Если обновляются товары
            if ($itemsData && count($itemsData) > 0) {
                $this->restoreStockFromOrder($order);
                $order->items()->delete();
                $total = $this->createOrderItems($order, $itemsData);
                $order->update(['subtotal' => $total, 'total_amount' => $total]);
            }

            // Обработка изменения статуса
            if (isset($validatedData['status'])) {
                $this->handleStatusChangeStock($order, $validatedData['status']);
            }

            $order->update([
                'user_id' => $validatedData['user_id'] ?? $order->user_id,
                'customer_name' => $validatedData['customer_name'] ?? $order->customer_name,
                'customer_phone' => $validatedData['customer_phone'] ?? $order->customer_phone,
                'customer_email' => $validatedData['customer_email'] ?? $order->customer_email,
                'shipping_method' => $validatedData['shipping_method'] ?? $order->shipping_method,
                'shipping_address' => $validatedData['shipping_address'] ?? $order->shipping_address,
                'status' => $validatedData['status'] ?? $order->status,
                'is_instagram' => $validatedData['is_instagram'] ?? $order->is_instagram,
            ]);

            return $order;
        });
    }

    /**
     * Создание позиций заказа
     */
    protected function createOrderItems(Order $order, array $itemsData): float
    {
        $total = 0;
        
        foreach ($itemsData as $data) {
            $product = Product::with('variants')->find($data['product_id']);
            if (!$product) {
                continue;
            }

            $lineTotal = $data['price'] * $data['quantity'];
            $total += $lineTotal;

            $size = $this->determineProductSize($product, $data['size'] ?? null);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'size' => $size,
                'quantity' => $data['quantity'],
                'price' => $data['price'],
                'total' => $lineTotal,
            ]);

            $this->adjustStock($product, $size, $data['quantity'], 'decrement');
        }

        return $total;
    }

    /**
     * Обработка изменения статуса заказа
     */
    public function handleStatusChangeStock(Order $order, string $newStatus): void
    {
        if ($newStatus === 'cancelled' && $order->status !== 'cancelled') {
            $this->restoreStockFromOrder($order);
        }
        
        if ($order->status === 'cancelled' && $newStatus !== 'cancelled') {
            $this->reserveStockForOrder($order);
        }
    }

    /**
     * Возврат товаров на склад
     */
    public function restoreStockFromOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $this->adjustStock($product, $item->size, $item->quantity, 'increment');
            }
        }
    }

    /**
     * Резервирование товаров со склада
     */
    public function reserveStockForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $this->adjustStock($product, $item->size, $item->quantity, 'decrement');
            }
        }
    }

    /**
     * Корректировка остатков
     */
    protected function adjustStock(Product $product, ?string $size, int $quantity, string $action): void
    {
        $method = $action === 'increment' ? 'increment' : 'decrement';
        
        $product->$method('stock_quantity', $quantity);
        
        if ($size && $size !== 'One Size') {
            $variant = $product->variants()->where('size', $size)->first();
            if ($variant) {
                $variant->$method('stock', $quantity);
            }
        }
    }

    /**
     * Определение размера товара
     */
    protected function determineProductSize(Product $product, ?string $size): string
    {
        if ($product->variants->isNotEmpty()) {
            return $size ?: $product->variants->first()->size;
        }
        
        return 'One Size';
    }

    /**
     * Расчет общей суммы заказа
     */
    protected function calculateOrderTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * Генерация уникального номера заказа
     */
    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());
        
        return $number;
    }

    /**
     * Обновление только статуса заказа
     */
    public function updateOrderStatus(Order $order, string $status, bool $isInstagram = false): void
    {
        DB::transaction(function () use ($order, $status, $isInstagram) {
            $this->handleStatusChangeStock($order, $status);
            
            $order->update([
                'status' => $status,
                'is_instagram' => $isInstagram
            ]);
        });
    }
}