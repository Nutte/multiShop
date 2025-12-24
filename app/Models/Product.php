<?php
// FILE: app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PromoCode;
use App\Services\TenantService;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'sale_price',
        'sku', 'stock_quantity', 'attributes', 'clothing_line_id',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order', 'asc');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    
    public function clothingLine()
    {
        return $this->belongsTo(ClothingLine::class);
    }

    // Скидки
    public function getHasDiscountAttribute()
    {
        return $this->sale_price && $this->sale_price < $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->has_discount) return 0;
        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    public function getCurrentPriceAttribute()
    {
        return $this->has_discount ? $this->sale_price : $this->price;
    }

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

    /**
     * Возвращает список активных промокодов, применимых к этому товару.
     * ВНИМАНИЕ: Это не самый оптимизированный метод для списков из 1000 товаров,
     * но для админки и карточки товара подойдет.
     */
    public function getApplicablePromosAttribute()
    {
        $tenantId = TenantService::getStaticCurrentTenantId();
        
        // Получаем все активные промокоды
        $promos = PromoCode::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();

        $applicable = [];

        foreach ($promos as $promo) {
            $scope = $promo->scope_type;
            $data = $promo->scope_data ?? [];

            // Если промокод не содержит данных для этого магазина (и не глобальный), пропускаем
            if ($scope !== 'global' && !isset($data[$tenantId])) {
                continue;
            }

            $match = false;

            if ($scope === 'global') {
                $match = true;
            } elseif ($scope === 'specific') {
                // Проверяем ID товара
                if (in_array($this->id, $data[$tenantId] ?? [])) {
                    $match = true;
                }
            } elseif ($scope === 'category') {
                // Проверяем категории
                $productCatSlugs = $this->categories->pluck('slug')->toArray();
                $promoCatSlugs = $data[$tenantId] ?? [];
                if (!empty(array_intersect($productCatSlugs, $promoCatSlugs))) {
                    $match = true;
                }
            } elseif ($scope === 'line') {
                // Проверяем линейку
                if ($this->clothingLine && in_array($this->clothingLine->slug, $data[$tenantId] ?? [])) {
                    $match = true;
                }
            }

            if ($match) {
                $applicable[] = $promo;
            }
        }

        return collect($applicable);
    }
}