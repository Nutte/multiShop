<?php
// FILE: database/migrations/2024_01_01_000021_update_users_and_orders_for_clients.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Обновляем таблицу users (добавляем телефон)
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone')->unique()->nullable();
                }
                // Роль уже есть в стандартной миграции или сидере, но на всякий случай
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->default('client'); // client, manager, super_admin
                }
            });
        }

        // 2. Обновляем таблицу orders (связь с юзером)
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};