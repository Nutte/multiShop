<?php
// FILE: app/Models/OrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'sku', 'size', 'quantity', 'price', 'total'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed(); // Даже если товар удален, связь нужна
    }
}