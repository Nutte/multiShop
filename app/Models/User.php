<?php
// FILE: app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',      
        'role',       
        'tenant_id',
        'access_key', // <--- Добавлено
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'access_key', // Скрываем при конвертации в массив (безопасность)
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'access_key' => 'encrypted', // <--- АВТОМАТИЧЕСКОЕ ШИФРОВАНИЕ/ДЕШИФРОВКА
    ];

    public function orders()
    {
        return $this->hasMany(Order::class)->latest();
    }
}