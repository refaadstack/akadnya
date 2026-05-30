<?php

use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checkout page requires authentication', function () {
    $template = Template::factory()->create();

    $response = $this->get("/checkout?template={$template->slug}");

    $response->assertRedirect('/login');
});

test('checkout page requires template parameter', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/checkout');

    $response->assertStatus(400);
});

test('checkout page can be rendered', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true]);
    Product::factory()->base()->create();
    Product::factory()->count(3)->create();

    $response = $this->actingAs($user)->get("/checkout?template={$template->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Checkout/Index')
        ->has('template')
        ->has('basePackage')
        ->has('addons')
    );
});

test('checkout page shows correct template', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create([
        'name' => 'Romantic',
        'slug' => 'romantic',
        'is_active' => true,
    ]);
    Product::factory()->base()->create();

    $response = $this->actingAs($user)->get("/checkout?template={$template->slug}");

    $response->assertInertia(fn ($page) => $page
        ->where('template.name', 'Romantic')
        ->where('template.slug', 'romantic')
    );
});

test('checkout page shows base package', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true]);
    $basePackage = Product::factory()->base()->create();

    $response = $this->actingAs($user)->get("/checkout?template={$template->slug}");

    $response->assertInertia(fn ($page) => $page
        ->where('basePackage.name', 'Paket Undangan Seumur Hidup')
        ->where('basePackage.slug', 'base')
    );
});

test('checkout page shows addon products', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true]);
    Product::factory()->base()->create();
    Product::factory()->create(['slug' => 'custom_domain', 'name' => 'Custom Domain', 'type' => 'addon']);
    Product::factory()->create(['slug' => 'managed_setup', 'name' => 'Managed Setup', 'type' => 'addon']);

    $response = $this->actingAs($user)->get("/checkout?template={$template->slug}");

    $response->assertInertia(fn ($page) => $page
        ->has('addons', 2)
    );
});

test('checkout page does not show inactive products', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true]);
    Product::factory()->base()->create();
    Product::factory()->create(['slug' => 'addon1', 'type' => 'addon', 'is_active' => true]);
    Product::factory()->create(['slug' => 'addon2', 'type' => 'addon', 'is_active' => false]);

    $response = $this->actingAs($user)->get("/checkout?template={$template->slug}");

    $response->assertInertia(fn ($page) => $page
        ->has('addons', 1) // Only 1 active addon
    );
});

test('checkout page requires verified email', function () {
    $user = User::factory()->unverified()->create();
    $template = Template::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->get("/checkout?template={$template->slug}");

    $response->assertRedirect('/email/verify');
});
