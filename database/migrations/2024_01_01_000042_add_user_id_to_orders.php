<?php

// File - database/migrations/2024_01_01_000042_add_user_id_to_orders.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tenants = config('tenants.tenants');
        
        foreach ($tenants as $tenantId => $config) {
            $schema = $config['db_schema'] ?? $tenantId;
            
            try {
                // Переключаемся на схему магазина
                DB::statement("SET search_path TO \"{$schema}\"");
                
                if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'user_id')) {
                    Schema::table('orders', function (Blueprint $table) {
                        // Ссылка на таблицу users в схеме public
                        // В Postgres cross-schema foreign keys работают, но требуют точного указания
                        // Laravel миграции иногда капризничают с кросс-схемными связями,
                        // поэтому делаем просто bigInteger индекс, без жесткого constrained() на уровне БД,
                        // либо указываем public.users явно, если драйвер поддерживает.
                        // Для надежности делаем просто поле, целостность обеспечим кодом.
                        $table->unsignedBigInteger('user_id')->nullable()->after('order_number');
                        $table->index('user_id');
                    });
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Возвращаем на public
        DB::statement("SET search_path TO public");
    }

    public function down(): void
    {
        // Логика отката
    }
};