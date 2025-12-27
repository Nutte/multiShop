<?php
// FILE: database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\ClothingLine;
use App\Models\AttributeOption;
use App\Services\TenantService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function run(): void
    {
        // ==========================================
        // 1. СОЗДАНИЕ СУПЕР-АДМИНА (PUBLIC СХЕМА)
        // ==========================================
        
        // ВАЖНО: Используем admin@trishop.com, как вы просили
        $adminEmail = 'admin@trishop.com';
        
        // firstOrCreate предотвращает дублирование, если сидер запущен повторно
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Super Admin',
                'phone' => '+380000000000',
                'password' => Hash::make('password'), // Пароль
                'role' => 'super_admin',
                'tenant_id' => null, // Доступ ко всем магазинам
                'access_key' => 'password', // Ключ для тестов
            ]
        );

        // Если пользователь существовал, но пароль не подходил, можно принудительно обновить:
        if ($admin->wasRecentlyCreated === false) {
             $admin->update([
                 'password' => Hash::make('password'),
                 'access_key' => 'password',
                 'role' => 'super_admin'
             ]);
        }

        // ==========================================
        // 2. СОЗДАНИЕ МЕНЕДЖЕРА
        // ==========================================
        $managerEmail = 'manager@street.local';

        User::firstOrCreate(
            ['email' => $managerEmail],
            [
                'name' => 'Street Manager',
                'phone' => '+380999999999',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'tenant_id' => 'street_style',
                'access_key' => 'password',
            ]
        );

        // ==========================================
        // 3. НАПОЛНЕНИЕ МАГАЗИНОВ
        // ==========================================
        $this->seedStreetStyle();
        $this->seedDesignerHub();
        $this->seedMilitaryGear();
    }

    // --- STREET STYLE ---
    private function seedStreetStyle()
    {
        $this->tenantService->switchTenant('street_style');
        if (Product::count() > 0) return;

        $this->createLine('Urban Summer 2025');
        
        $products = [
            [
                'name' => 'Oversized Graffiti Hoodie',
                'sku' => 'ST-001', 'price' => 89.99, 'sale_price' => 69.99,
                'category' => 'Hoodies', 'type' => 'Hoodie', 'line' => 'Urban Summer 2025',
                'sizes' => ['M', 'L', 'XL'],
                'desc' => 'Cotton hoodie with graffiti print.'
            ],
            [
                'name' => 'Cargo Joggers Black',
                'sku' => 'ST-002', 'price' => 59.99, 'sale_price' => null,
                'category' => 'Pants', 'type' => 'Pants', 'line' => 'Urban Summer 2025',
                'sizes' => ['30', '32', '34'],
                'desc' => 'Tactical cargo pants with many pockets.'
            ],
            [
                'name' => 'Neon Bucket Hat',
                'sku' => 'ST-003', 'price' => 25.00, 'sale_price' => null,
                'category' => 'Accessories', 'type' => 'Hat', 'line' => null,
                'sizes' => ['One Size'],
                'desc' => 'Bright neon hat for parties.'
            ]
        ];

        foreach ($products as $p) $this->createProduct($p);
    }

    // --- DESIGNER HUB ---
    private function seedDesignerHub()
    {
        $this->tenantService->switchTenant('designer_hub');
        if (Product::count() > 0) return;

        $this->createLine('Milano Evening Collection');

        $products = [
            [
                'name' => 'Silk Evening Gown Red',
                'sku' => 'DH-501', 'price' => 450.00, 'sale_price' => 399.00,
                'category' => 'Dresses', 'type' => 'Dress', 'line' => 'Milano Evening Collection',
                'sizes' => ['XS', 'S', 'M'],
                'desc' => '100% Italian Silk. Handcrafted in Milan.'
            ],
            [
                'name' => 'Slim Fit Tuxedo',
                'sku' => 'DH-502', 'price' => 899.00, 'sale_price' => null,
                'category' => 'Suits', 'type' => 'Suit', 'line' => 'Milano Evening Collection',
                'sizes' => ['48', '50', '52'],
                'desc' => 'Classic black tuxedo for formal events.'
            ]
        ];

        foreach ($products as $p) $this->createProduct($p);
    }

    // --- MILITARY GEAR ---
    private function seedMilitaryGear()
    {
        $this->tenantService->switchTenant('military_gear');
        if (Product::count() > 0) return;

        $this->createLine('Tactical Ops');

        $products = [
            [
                'name' => 'Combat Boots Desert',
                'sku' => 'MG-901', 'price' => 149.99, 'sale_price' => 129.99,
                'category' => 'Footwear', 'type' => 'Boots', 'line' => 'Tactical Ops',
                'sizes' => ['42', '43', '44', '45'],
                'desc' => 'Durable combat boots for rough terrain.'
            ],
            [
                'name' => 'Camouflage Tactical Vest',
                'sku' => 'MG-902', 'price' => 85.00, 'sale_price' => null,
                'category' => 'Vests', 'type' => 'Vest', 'line' => 'Tactical Ops',
                'sizes' => ['M', 'L', 'XL'],
                'desc' => 'Lightweight vest with MOLLE system.'
            ]
        ];

        foreach ($products as $p) $this->createProduct($p);
    }

    // --- HELPERS ---

    private function createLine($name)
    {
        if (!$name) return null;
        return ClothingLine::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
    }

    private function createProduct($data)
    {
        $cat = Category::firstOrCreate(
            ['slug' => Str::slug($data['category'])],
            ['name' => $data['category']]
        );

        $lineId = null;
        if ($data['line']) {
            $line = ClothingLine::where('name', $data['line'])->first();
            $lineId = $line ? $line->id : null;
        }

        AttributeOption::firstOrCreate(['type' => 'product_type', 'slug' => Str::slug($data['type'])], ['value' => $data['type']]);
        foreach ($data['sizes'] as $size) {
            AttributeOption::firstOrCreate(['type' => 'size', 'slug' => Str::slug($size)], ['value' => $size]);
        }

        $product = Product::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'sku' => $data['sku'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'],
            'stock_quantity' => 100,
            'description' => $data['desc'],
            'clothing_line_id' => $lineId,
            'attributes' => [
                'type' => $data['type'],
                'size' => $data['sizes']
            ]
        ]);

        $product->categories()->attach($cat->id);
        
        foreach ($data['sizes'] as $size) {
            $product->variants()->create([
                'size' => $size,
                'stock' => 20
            ]);
        }

        $text = urlencode($data['name']);
        $product->images()->create([
            'path' => "https://placehold.co/600x600/e2e8f0/1e293b?text={$text}",
            'sort_order' => 0
        ]);
    }
}