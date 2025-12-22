<?php
// FILE: app/Console/Commands/TenantsStorageLinkCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TenantsStorageLinkCommand extends Command
{
    protected $signature = 'tenants:link';
    protected $description = 'Create symbolic links for tenants (public/tenants/{id} -> storage/tenants/{id})';

    public function handle()
    {
        $tenants = config('tenants.tenants');
        
        // Создаем папку-контейнер в public, если нет
        if (!File::exists(public_path('tenants'))) {
            File::makeDirectory(public_path('tenants'), 0755, true);
        }

        foreach ($tenants as $id => $config) {
            // Цель: storage/tenants/{id}/media
            // Мы линкуем корень тенанта или конкретно media. 
            // Для удобства слинкуем корень тенанта, чтобы иметь доступ и к другим публичным папкам если понадобятся.
            
            $target = storage_path("tenants/{$id}"); 
            $link = public_path("tenants/{$id}");

            // 1. Создаем целевую папку в storage, если её нет
            if (!File::exists($target)) {
                 $this->warn("Target directory [{$target}] does not exist. Creating...");
                 // force creation
                 File::makeDirectory($target, 0777, true);
                 // Создаем внутри media для порядка
                 File::makeDirectory($target . '/media', 0777, true); 
            }

            // 2. Если ссылка уже есть — пропускаем
            if (File::exists($link)) {
                $this->info("The [{$link}] link already exists.");
                continue;
            }

            // 3. Создаем симлинк
            try {
                $this->laravel->make('files')->link($target, $link);
                $this->info("Connected: [public/tenants/{$id}] -> [storage/tenants/{id}]");
            } catch (\Exception $e) {
                $this->error("Failed to link {$id}: " . $e->getMessage());
            }
        }

        $this->info('All tenant links processed.');
    }
}