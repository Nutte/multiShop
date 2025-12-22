<?php
// FILE: database/migrations/2024_01_01_000003_create_tenant_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'telegram_chat_id', 'novaposhta_api_key'
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // 'notifications', 'delivery'
            $table->timestamps();
        });
        
        // Добавим роль пользователям, если еще нет
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('manager'); // 'super_admin', 'manager'
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};