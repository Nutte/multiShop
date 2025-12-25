<?php
// FILE: app/Models/User.php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// УДАЛИТЕ ИЛИ ЗАКОММЕНТИРУЙТЕ ЭТУ СТРОКУ:
// use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // УБЕРИТЕ HasApiTokens ИЗ СПИСКА USE:
    use HasFactory, Notifiable; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',      
        'role',       
        'tenant_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Связь с заказами
    public function orders()
    {
        return $this->hasMany(Order::class)->latest();
    }
}