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
        'attributes',
        'clothing_line_id', // Добавлено новое поле
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

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    
    // Новая связь: Линейка одежды
    public function clothingLine()
    {
        return $this->belongsTo(ClothingLine::class);
    }

    // Хелперы URL
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