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
        'user_id',          // <--- ДОБАВЛЕНО! Теперь заказ будет привязываться
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_method',
        'shipping_address',
        'subtotal',
        'discount_amount',
        'total_amount',
        'promo_code',
        'status',
        'payment_method',
        'payment_status'
    ];

    // Связь с товарами
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // Связь с пользователем (клиентом)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Хелпер для цветов статуса в админке
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'new' => 'blue',
            'processing' => 'yellow',
            'shipped' => 'purple',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}