<?php
// FILE: app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * ВАЖНО: Принудительно указываем таблицу в схеме public.
     * Это решает проблему авторизации, когда система ищет юзера в схеме магазина (tenant_1.users),
     * а он находится в public.users.
     */
    protected $table = 'public.users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',      
        'role',       
        'tenant_id',
        'access_key', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'access_key', 
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Автоматическое хеширование (поэтому в контроллерах Hash::make не нужен)
        'access_key' => 'encrypted', 
    ];
    public function orders(){
        return $this->hasMany(Order::class)->latest();
    }
    
    protected function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = self::normalizePhone($value);
    }

    public static function normalizePhone($phone)
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 10) {
            $digits = '38' . $digits;
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '8')) {
            $digits = '3' . $digits; 
        } elseif (strlen($digits) === 9) { 
             $digits = '380' . $digits;
        }

        return '+' . $digits;
    }
}