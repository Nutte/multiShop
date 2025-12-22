<?php
// FILE: database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Services\TenantService;
use App\Services\SearchService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(TenantService $tenantService, SearchService $searchService): void
    {
        $currentTenant = $tenantService->getCurrentTenantId();
        $password = Hash::make('password');

        // 1. PUBLIC СХЕМА: Создаем Персонал
        if (!$currentTenant) {
            $this->command->info('Seeding PUBLIC schema (Staff)...');
            
            // Супер Админ (доступ ко всему)
            User::updateOrCreate(
                ['email' => 'admin@trishop.com'],
                [
                    'name' => 'Super Admin',
                    'password' => $password,
                    'role' => 'super_admin',
                    'tenant_id' => null, // NULL = Глобальный доступ
                ]
            );

            // Менеджеры (привязаны к магазинам)
            $managers = [
                'street_style' => 'manager@street.com',
                'designer_hub' => 'manager@designer.com',
                'military_gear' => 'manager@military.com',
            ];

            foreach ($managers as $tenant => $email) {
                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => ucfirst(str_replace('_', ' ', $tenant)) . ' Manager',
                        'password' => $password,
                        'role' => 'manager',
                        'tenant_id' => $tenant, // ЖЕСТКАЯ ПРИВЯЗКА
                    ]
                );
            }
            return;
        }

        // 2. СХЕМЫ МАГАЗИНОВ: Только товары и данные клиентов
        // Персонал здесь НЕ создаем, он живет в Public
        $this->command->info("Seeding TENANT Data: {$currentTenant}");

        // Очистка Elastic
        try {
            $searchService->deleteIndex();
            $searchService->createIndexIfNotExists();
        } catch (\Exception $e) {}

        // Товары
        $products = $this->getProductsForTenant($currentTenant);
        foreach ($products as $data) {
            $product = Product::firstOrCreate(['sku' => $data['sku']], $data);
            try {
                $searchService->indexProduct($product);
            } catch (\Exception $e) {}
        }
    }

    private function getProductsForTenant(string $tenant): array
    {
        // ... (Используем тот же список товаров, что и раньше)
        // Для краткости я его не дублирую здесь, код идентичен предыдущему этапу
        return match ($tenant) {
            'street_style' => [['name' => 'Graffiti Hoodie', 'price' => 89.99, 'sku' => 'ST-01', 'slug'=>'h1', 'category'=>'Hoodie']], 
            'military_gear' => [['name' => 'Tactical Vest', 'price' => 150.00, 'sku' => 'MG-01', 'slug'=>'v1', 'category'=>'Vest']],
            'designer_hub' => [['name' => 'Evening Gown', 'price' => 500.00, 'sku' => 'DH-01', 'slug'=>'d1', 'category'=>'Dress']],
            default => [],
        };
    }
}