<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Base Packages - User can choose one
            [
                'type' => 'base_package',
                'slug' => 'monthly',
                'name' => 'Paket Bulanan',
                'description' => 'Paket undangan digital untuk 1 bulan',
                'price' => 29000,
                'is_active' => true,
                'is_recurring' => true,
                'recurring_interval' => 'monthly',
                'metadata' => [
                    'duration_days' => 30,
                    'is_recurring' => true,
                    'recurring_interval' => 'monthly',
                ],
            ],
            [
                'type' => 'base_package',
                'slug' => 'yearly',
                'name' => 'Paket Tahunan',
                'description' => 'Paket undangan digital untuk 1 tahun',
                'price' => 79000,
                'is_active' => true,
                'is_recurring' => true,
                'recurring_interval' => 'yearly',
                'metadata' => [
                    'duration_days' => 365,
                    'is_recurring' => true,
                    'recurring_interval' => 'yearly',
                ],
            ],
            [
                'type' => 'base_package',
                'slug' => 'lifetime',
                'name' => 'Paket Seumur Hidup',
                'description' => 'Paket undangan digital selamanya',
                'price' => 99000,
                'is_active' => true,
                'is_recurring' => false,
                'recurring_interval' => null,
                'metadata' => [
                    'duration_days' => null,
                    'is_recurring' => false,
                ],
            ],
            // Add-ons
            [
                'type' => 'addon',
                'slug' => 'custom_domain',
                'name' => 'Custom Domain',
                'description' => 'Gunakan domain pribadi Anda untuk undangan',
                'price' => 49000,
                'is_active' => true,
                'is_recurring' => false,
                'recurring_interval' => null,
                'metadata' => null,
            ],
            [
                'type' => 'addon',
                'slug' => 'managed_setup',
                'name' => 'Dibantu Setup oleh Tim',
                'description' => 'Tim kami akan membantu setup undangan Anda',
                'price' => 79000,
                'is_active' => true,
                'is_recurring' => false,
                'recurring_interval' => null,
                'metadata' => null,
            ],
            [
                'type' => 'addon',
                'slug' => 'extra_storage',
                'name' => 'Tambah Storage 1GB',
                'description' => 'Tambahan storage untuk foto dan video lebih banyak',
                'price' => 29000,
                'is_active' => true,
                'is_recurring' => false,
                'recurring_interval' => null,
                'metadata' => ['storage_gb' => 1],
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                $product
            );
        }

        $this->command->info('Products seeded successfully!');
    }
}
