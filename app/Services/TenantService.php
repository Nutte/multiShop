<?php
// FILE: app/Services/TenantService.php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TenantService
{
    private ?string $currentTenantId = null;

    /**
     * Переключает контекст приложения на указанного тенанта (схему БД).
     */
    public function switchTenant(string $tenantId): void
    {
        // 1. Проверка существования конфигурации
        $tenantConfig = config("tenants.tenants.{$tenantId}");
        if (!$tenantConfig) {
            throw new \Exception("Tenant {$tenantId} not found in config.");
        }

        $this->currentTenantId = $tenantId;

        // 2. Переключение PostgreSQL (Schema)
        // Мы меняем search_path. Это заставляет Postgres искать таблицы сначала в схеме тенанта.
        // public добавляем в конец, чтобы были доступны общие таблицы (если понадобятся).
        DB::purge('pgsql'); // Закрываем старое соединение
        Config::set('database.connections.pgsql.search_path', "{$tenantId}, public");
        DB::reconnect('pgsql'); // Открываем новое с новыми настройками

        // 3. Настройка файловой системы
        // Путь будет: storage/tenants/{tenant_id}/
        Config::set('filesystems.disks.tenant.root', storage_path("tenants/{$tenantId}"));
        Config::set('filesystems.disks.tenant.url', env('APP_URL') . "/storage/tenants/{$tenantId}");

        // 4. Настройка Redis (Prefix)
        // Чтобы ключи кэша разных магазинов не пересекались
        Config::set('database.redis.options.prefix', "trishop_{$tenantId}_");
        // Пересоздаем фасад Redis, чтобы он подхватил новый префикс (если соединение уже было открыто)
        try {
            Redis::connection()->disconnect();
        } catch (\Exception $e) {
            // Игнорируем ошибку отключения, если соединения не было
        }

        // Логируем переключение (полезно для отладки)
        Log::info("Switched to tenant: {$tenantId}");
    }

    /**
     * Возвращает ID текущего тенанта или null, если мы в глобальной зоне (public).
     */
    public function getCurrentTenantId(): ?string
    {
        return $this->currentTenantId;
    }

    /**
     * Получить список всех доменов для Middleware
     */
    public function getDomainMap(): array
    {
        $map = [];
        foreach (config('tenants.tenants') as $id => $data) {
            $map[$data['domain']] = $id;
        }
        return $map;
    }
}