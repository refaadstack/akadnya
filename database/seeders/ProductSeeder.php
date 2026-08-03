<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Templates include full access, so products are add-ons only.
     */
    public function run(): void
    {
        $products = [
            [
                'type' => 'addon',
                'slug' => 'guest_book',
                'name' => 'Buku Tamu Digital',
                'description' => 'Buku tamu digital lengkap dengan ucapan dan tanda tangan tamu',
                'price' => 19000,
                'is_active' => true,
                'is_recurring' => false,
                'recurring_interval' => null,
                'metadata' => null,
            ],
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
