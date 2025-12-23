<?php
// FILE: app/Models/TelegramConfig.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramConfig extends Model
{
    protected $fillable = [
        'name',
        'bot_token',
        'chat_id',
        'tenant_id',
        'is_active',
    ];

    // Явно указываем, что таблица находится в схеме public (если вы используете PostgreSQL схемы)
    // Это гарантирует, что модель всегда обращается к центральным настройкам, 
    // даже если мы переключили search_path на конкретный магазин.
    protected $table = 'public.telegram_configs';
}