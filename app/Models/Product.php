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
        'stock_quantity', 
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

    // Основная логика получения обложки
    public function getCoverUrlAttribute()
    {
        // Берем первое изображение из коллекции (благодаря orderBy в связи оно будет с sort_order=0)
        $cover = $this->images->first();
        
        if ($cover) {
            return $cover->url;
        }
        
        // Дефолтная заглушка, если картинок нет
        $text = urlencode($this->sku ?? 'Product');
        return "https://placehold.co/600x600/e2e8f0/1e293b?text={$text}";
    }

    // --- FIX: Обратная совместимость для админки ---
    // Этот метод позволяет использовать $product->image_url в старых шаблонах
    public function getImageUrlAttribute()
    {
        return $this->cover_url;
    }
}