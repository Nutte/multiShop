<?php
// FILE: app/Console/Commands/TenantsMigrateCommand.php

namespace App\Console\Commands;

use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantsMigrateCommand extends Command
{
    protected $signature = 'tenants:migrate {--fresh : Wipe databases first} {--seed : Run seeders}';
    protected $description = 'Run migrations for Public and ALL tenants';

    public function handle(TenantService $tenantService)
    {
        $tenants = config('tenants.tenants');
        
        $this->info("1. PROCESSING PUBLIC SCHEMA...");
        
        // Сбрасываем контекст на Public
        DB::statement("SET search_path TO public");
        
        // 1. Накатываем структуру Public
        if ($this->option('fresh')) {
            $this->call('migrate:fresh', [
                '--force' => true,
                '--path' => 'database/migrations',
            ]);
        } else {
            $this->call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations',
            ]);
        }

        // 2. ИСПРАВЛЕНИЕ: Сеем Public схему (создаем Админа) СРАЗУ
        if ($this->option('seed')) {
            $this->info("Seeding PUBLIC schema...");
            $this->call('db:seed', ['--force' => true]); 
            // DatabaseSeeder увидит, что tenant не переключен, и создаст Админа.
        }

        // 3. Обработка Тенантов
        foreach ($tenants as $tenantId => $config) {
            $this->info("---------------------------------------");
            $this->info("2. PROCESSING TENANT: {$config['name']} ($tenantId)");

            DB::statement("CREATE SCHEMA IF NOT EXISTS \"{$tenantId}\"");

            $tenantService->switchTenant($tenantId);

            $this->call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations',
            ]);

            if ($this->option('seed')) {
                $this->info("Seeding tenant {$tenantId}...");
                $this->call('db:seed', ['--force' => true]);
            }
        }

        $this->info("---------------------------------------");
        $this->info("✅ All operations completed successfully.");
    }
}