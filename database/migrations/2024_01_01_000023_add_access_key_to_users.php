<?php

// FILE: database/migrations/2024_01_01_000023_add_access_key_to_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Поле для хранения зашифрованного ключа доступа
                // text, так как зашифрованная строка длинная
                $table->text('access_key')->nullable();
            });
        }
    }

    public function down(): void
    {
        //
    }
};