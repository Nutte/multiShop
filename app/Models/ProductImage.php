<?php
// FILE: app/Models/ProductImage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'sort_order'];

    // Аксессор для URL
    public function getUrlAttribute()
    {
        // Если это внешняя ссылка (например, с генератора заглушек), возвращаем как есть
        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        // Иначе генерируем ссылку через диск тенанта
        return Storage::disk('tenant')->url($this->path);
    }
}