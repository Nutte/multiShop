<?php
// FILE: app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'slug', 
        'description', 
        'price', 
        'sku', 
        'stock_quantity', // Общее количество (сумма вариантов)
        'attributes'
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order', 'asc');
    }

    // Новая связь: Варианты (Размеры + Количество)
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Получить остаток для конкретного размера
    public function getStockForSize($size)
    {
        // Если вариантов нет (товар без размеров), возвращаем общий сток
        if ($this->variants->isEmpty()) {
            return $this->stock_quantity;
        }
        
        $variant = $this->variants->where('size', $size)->first();
        return $variant ? $variant->stock : 0;
    }

    // Метод уменьшения остатка (вызывается при заказе)
    public function decreaseStock(string $size, int $quantity = 1)
    {
        $telegram = app(\App\Services\TelegramService::class);
        $tenantName = app(\App\Services\TenantService::class)->getCurrentTenantId() ?? 'Unknown';

        // 1. Если есть варианты (размерный товар)
        if ($this->variants->count() > 0) {
            $variant = $this->variants()->where('size', $size)->first();
            
            if ($variant && $variant->stock >= $quantity) {
                $variant->decrement('stock', $quantity);
                
                // Проверка на 0
                if ($variant->stock <= 0) {
                    $telegram->sendStockAlert($this->name, $size, $tenantName);
                }
            }
        } 
        // 2. Если товар безразмерный (используем общее поле)
        else {
            if ($this->stock_quantity >= $quantity) {
                $this->decrement('stock_quantity', $quantity);
                if ($this->stock_quantity <= 0) {
                    $telegram->sendStockAlert($this->name, 'One Size', $tenantName);
                }
            }
        }

        // Обновляем общий счетчик товара (сумма всех вариантов)
        $this->recalculateTotalStock();
    }

    public function recalculateTotalStock()
    {
        if ($this->variants()->count() > 0) {
            $total = $this->variants()->sum('stock');
            $this->update(['stock_quantity' => $total]);
        }
    }

    // Обложка (старый код)
    public function getCoverUrlAttribute()
    {
        $cover = $this->images->first();
        if ($cover) return $cover->url;
        $text = urlencode($this->sku ?? 'Product');
        return "https://placehold.co/600x600/e2e8f0/1e293b?text={$text}";
    }

    public function getImageUrlAttribute()
    {
        return $this->cover_url;
    }
}