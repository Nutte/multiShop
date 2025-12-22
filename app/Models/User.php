<?php
// FILE: app/Models/User.php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
        'role',      // super_admin, manager
        'tenant_id', // Привязка к конкретному магазину (null для супер-админа)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Хелпер для проверки доступа
    public function hasAccessToTenant(string $tenantId): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }
        return $this->tenant_id === $tenantId;
    }
}