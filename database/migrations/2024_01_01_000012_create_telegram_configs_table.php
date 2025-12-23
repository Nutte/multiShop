<?php
// FILE: database/migrations/2024_01_01_000012_create_telegram_configs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Создаем таблицу в схеме public, так как это глобальные настройки
        // Но Laravel по умолчанию использует текущий search_path.
        // Мы предполагаем, что эта миграция запустится один раз для центральной БД.
        
        Schema::create('telegram_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название (для удобства, например "StreetStyle Bot")
            $table->string('bot_token'); // Токен от BotFather
            $table->string('chat_id'); // ID чата или группы для уведомлений
            $table->string('tenant_id')->nullable()->unique(); // Привязка к магазину (null = общий бот)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_configs');
    }
};