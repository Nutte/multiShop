<?php
// FILE: app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'slug', 
        'description', 
        'image_path',
        'price', 
        // 'category_id' удален
        'sku', 
        'stock_quantity', 
        'attributes' // JSON оставим для быстрого чтения, но значения будем брать из справочников
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return 'https://via.placeholder.com/300x300?text=No+Image';
        }
        return Storage::disk('tenant')->url($this->image_path);
    }
}