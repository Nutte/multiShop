<?php
// FILE: app/Console/Commands/TenantsLinkCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TenantsLinkCommand extends Command
{
    // Добавляем опцию --force
    protected $signature = 'tenants:link {--force : Overwrite existing links}';
    protected $description = 'Create symbolic links for tenants storage';

    public function handle()
    {
        $tenants = config('tenants.tenants');
        $force = $this->option('force');
        
        // Убедимся, что папка public/tenants существует
        if (!File::exists(public_path('tenants'))) {
            File::makeDirectory(public_path('tenants'));
        }

        foreach ($tenants as $id => $config) {
            $target = storage_path("tenants/{$id}");
            $link = public_path("tenants/{$id}");

            // Создаем физическую папку в storage, если нет
            if (!File::exists($target)) {
                File::makeDirectory($target, 0755, true);
                File::makeDirectory($target . '/media', 0755, true);
            }

            if (File::exists($link)) {
                if ($force) {
                    // Если force, удаляем старую ссылку (или папку, если это папка)
                    // Используем unlink для симлинков
                    if (is_link($link)) {
                        unlink($link);
                    } else {
                        // Если это вдруг реальная папка (ошибка структуры), удаляем рекурсивно
                        File::deleteDirectory($link);
                    }
                    $this->warn("Removed existing link: {$link}");
                } else {
                    $this->error("Link exists: {$link} (use --force to overwrite)");
                    continue;
                }
            }

            // Создаем симлинк
            $this->laravel->make('files')->link($target, $link);
            
            $this->info("Linked: public/tenants/{$id} -> storage/tenants/{$id}");
        }
        
        // Также линкуем стандартный storage с флагом force, если нужно
        $this->call('storage:link', $force ? ['--force' => true] : []);

        $this->info('All tenants linked.');
    }
}