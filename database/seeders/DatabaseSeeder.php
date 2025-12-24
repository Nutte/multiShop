<?php
// FILE: database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\AttributeOption;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $schema = DB::connection()->getPdo()->query('SELECT current_schema()')->fetchColumn();

        if ($schema === 'public') {
            $this->seedUsers();
            return;
        }

        match ($schema) {
            'street_style' => $this->seedStreetStyle(),
            'designer_hub' => $this->seedDesignerHub(),
            'military_gear' => $this->seedMilitaryGear(),
            default => null,
        };
    }

    private function seedUsers()
    {
        User::firstOrCreate(
            ['email' => 'admin@trishop.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'role' => 'super_admin', 'tenant_id' => null]
        );

        $managers = [
            ['email' => 'manager@street.com', 'tenant' => 'street_style', 'name' => 'Street Manager'],
            ['email' => 'manager@designer.com', 'tenant' => 'designer_hub', 'name' => 'Designer Manager'],
            ['email' => 'manager@military.com', 'tenant' => 'military_gear', 'name' => 'Military Manager'],
        ];

        foreach ($managers as $mgr) {
            User::firstOrCreate(
                ['email' => $mgr['email']],
                ['name' => $mgr['name'], 'password' => Hash::make('password'), 'role' => 'manager', 'tenant_id' => $mgr['tenant']]
            );
        }
    }

    private function seedProduct($data)
    {
        $categoryName = $data['category_name'];
        $slug = Str::slug($categoryName);
        $category = Category::firstOrCreate(['slug' => $slug], ['name' => $categoryName]);

        $attributes = $data['attributes'] ?? [];
        if (isset($attributes['type'])) {
            AttributeOption::firstOrCreate(['type' => 'product_type', 'slug' => Str::slug($attributes['type'])], ['value' => $attributes['type']]);
        }
        if (isset($attributes['size'])) {
            foreach ($attributes['size'] as $size) {
                AttributeOption::firstOrCreate(['type' => 'size', 'slug' => Str::slug($size)], ['value' => $size]);
            }
        }

        $product = Product::firstOrCreate(
            ['sku' => $data['sku']],
            [
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'sale_price' => $data['sale_price'] ?? null,
                'stock_quantity' => $data['stock'],
                'attributes' => $attributes,
            ]
        );

        $product->categories()->syncWithoutDetaching([$category->id]);

        // Создаем изображения ТОЛЬКО если их нет
        if ($product->images()->count() === 0) {
            foreach ($data['images'] as $index => $path) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'sort_order' => $index
                ]);
            }
        }
    }

    // Street Style: Желтый/Черный стиль
    private function seedStreetStyle()
    {
        $products = [
            [
                'name' => 'Oversized Graffiti Hoodie',
                'sku' => 'ST-001',
                'price' => 89.99,
                'sale_price' => 69.99, // СКИДКА!
                'stock' => 50,
                'description' => 'Premium cotton hoodie with urban graffiti print.',
                'category_name' => 'Hoodies',
                'attributes' => ['type' => 'Hoodie', 'size' => ['M', 'L', 'XL']],
                'images' => [
                    'https://placehold.co/600x800/facc15/000000?text=Graffiti+Hoodie+Front',
                    'https://placehold.co/600x800/facc15/000000?text=Graffiti+Hoodie+Back'
                ]
            ],
            [
                'name' => 'Urban Cargo Pants',
                'sku' => 'ST-002',
                'price' => 65.00,
                'stock' => 30,
                'description' => 'Black cargo pants with multiple pockets.',
                'category_name' => 'Pants',
                'attributes' => ['type' => 'Pants', 'size' => ['30', '32', '34']],
                'images' => [
                    'https://placehold.co/600x800/1f2937/ffffff?text=Cargo+Pants'
                ]
            ]
        ];
        foreach ($products as $p) $this->seedProduct($p);
    }

    // Designer Hub: Элегантный Черный/Белый
    private function seedDesignerHub()
    {
        $products = [
            [
                'name' => 'Silk Evening Dress',
                'sku' => 'DH-001',
                'price' => 450.00,
                'sale_price' => 69.99, // СКИДКА!
                'stock' => 10,
                'description' => 'Elegant black silk dress for special occasions.',
                'category_name' => 'Dresses',
                'attributes' => ['type' => 'Dress', 'size' => ['S', 'M']],
                'images' => [
                    'https://placehold.co/600x800/000000/ffffff?text=Silk+Dress',
                    'https://placehold.co/600x800/333333/ffffff?text=Dress+Detail'
                ]
            ],
            [
                'name' => 'Italian Leather Bag',
                'sku' => 'DH-002',
                'price' => 299.99,
                'stock' => 15,
                'description' => 'Handcrafted leather bag from Milan.',
                'category_name' => 'Accessories',
                'attributes' => ['type' => 'Accessory', 'size' => ['One Size']],
                'images' => [
                    'https://placehold.co/600x800/5c3a21/ffffff?text=Leather+Bag'
                ]
            ]
        ];
        foreach ($products as $p) $this->seedProduct($p);
    }

    // Military: Зеленый/Камуфляж
    private function seedMilitaryGear()
    {
        $products = [
            [
                'name' => 'Tactical Boots Gen-2',
                'sku' => 'MG-001',
                'price' => 120.00,
                'sale_price' => 69.99, // СКИДКА!
                'stock' => 100,
                'description' => 'Waterproof tactical boots for harsh terrain.',
                'category_name' => 'Footwear',
                'attributes' => ['type' => 'Boots', 'size' => ['42', '43', '44', '45']],
                'images' => [
                    'https://placehold.co/600x800/3f4f3a/ffffff?text=Tactical+Boots',
                    'https://placehold.co/600x800/2d3a29/ffffff?text=Boots+Sole'
                ]
            ],
            [
                'name' => 'Camo Field Jacket',
                'sku' => 'MG-002',
                'price' => 85.50,
                'stock' => 60,
                'description' => 'Durable field jacket with woodland camo pattern.',
                'category_name' => 'Jackets',
                'attributes' => ['type' => 'Jacket', 'size' => ['L', 'XL', 'XXL']],
                'images' => [
                    'https://placehold.co/600x800/4b5320/ffffff?text=Camo+Jacket'
                ]
            ]
        ];
        foreach ($products as $p) $this->seedProduct($p);
    }
}