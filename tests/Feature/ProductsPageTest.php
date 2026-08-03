<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('products page lists active products', function () {
    Product::factory()->base()->create();
    Product::factory()->create([
        'type' => 'addon',
        'slug' => 'custom_domain',
        'name' => 'Custom Domain',
        'price' => 49000,
    ]);
    Product::factory()->create([
        'type' => 'addon',
        'slug' => 'hidden',
        'is_active' => false,
    ]);

    $response = $this->get(route('products.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Products/Index')
        ->has('products', 2)
        ->where('products.0.slug', 'base')
        ->where('products.1.slug', 'custom_domain')
    );
});

test('products page can be empty', function () {
    $response = $this->get(route('products.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Products/Index')
        ->has('products', 0)
    );
});
