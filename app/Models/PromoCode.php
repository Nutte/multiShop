<?php
// FILE: app/Models/PromoCode.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    // Важно: таблица всегда в public схеме
    protected $table = 'public.promo_codes';

    protected $fillable = [
        'code', 
        'type', 
        'value', 
        'is_active', 
        'starts_at',
        'expires_at',
        'scope_type',
        'scope_data'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'scope_data' => 'array', // Авто-конвертация JSON
    ];

    // Проверка валидности по времени
    public function isValid()
    {
        if (!$this->is_active) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->expires_at && $now->gt($this->expires_at)) return false;
        
        return true;
    }
}