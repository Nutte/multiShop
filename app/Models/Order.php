<?php
// FILE: app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_method',
        'shipping_address',
        'payment_method',
        'payment_status',
        'subtotal',
        'discount_amount',
        'total_amount',
        'promo_code',
        'status',
        'is_instagram',
    ];

    protected $casts = [
        'is_instagram' => 'boolean',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    // ХЕЛПЕР ДЛЯ ЦВЕТОВ СТАТУСА (Возвращен по запросу)
    // Использование в Blade: {{ $order->status_color }}
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'new' => 'bg-blue-100 text-blue-800',
            'processing' => 'bg-yellow-100 text-yellow-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}