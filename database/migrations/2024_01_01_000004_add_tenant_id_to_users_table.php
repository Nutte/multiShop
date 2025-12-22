<?php
// FILE: database/migrations/2024_01_01_000004_add_tenant_id_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем поле и в public схему (для персонала), и в схемы тенантов (для клиентов)
        Schema::table('users', function (Blueprint $table) {
            // Если tenant_id NULL — это глобальный пользователь (Супер-админ)
            // Если заполнено (например 'military_gear') — доступ только к этому магазину
            $table->string('tenant_id')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }
};