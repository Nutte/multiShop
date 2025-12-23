<?php
// FILE: app/Services/TenantService.php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage; // Добавлен импорт

class TenantService
{
    // Статическое свойство для хранения ID, чтобы его видел config
    protected static ?string $currentTenantId = null;

    public function switchTenant(string $tenantId): void
    {
        $config = config("tenants.tenants.{$tenantId}");

        if (!$config) {
            throw new \Exception("Tenant {$tenantId} not found in config.");
        }

        // 1. Сохраняем в статику (для config/filesystems.php)
        self::$currentTenantId = $tenantId;

        // 2. Переключаем соединение с БД (search_path)
        DB::statement("SET search_path TO \"{$tenantId}\""); // Убрали public из пути для строгой изоляции
        
        // 3. [ВАЖНО] Динамически обновляем конфиг диска
        // Это заставит Storage::disk('tenant') смотреть в правильную папку
        Config::set('filesystems.disks.tenant.root', storage_path("tenants/{$tenantId}"));
        Config::set('filesystems.disks.tenant.url', "/tenants/{$tenantId}");

        // 4. [ВАЖНО] Сбрасываем кэшированный инстанс диска, чтобы Laravel пересоздал его с новым конфигом
        Storage::forgetDisk('tenant');
    }

    public function getCurrentTenantId(): ?string
    {
        return self::$currentTenantId;
    }
    
    // Метод для вызова из конфиг-файлов
    public static function getStaticCurrentTenantId(): ?string
    {
        return self::$currentTenantId;
    }

    public function getDomainMap(): array
    {
        $tenants = config('tenants.tenants');
        $map = [];
        foreach ($tenants as $id => $data) {
            $map[$data['domain']] = $id;
        }
        return $map;
    }
}