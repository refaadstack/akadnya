<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checkout page requires authentication', function () {
    $template = Template::factory()->create();

    $response = $this->get("/checkout?template={$template->slug}");

    $response->assertRedirect('/login');
});

test('checkout page redirects to templates when no item is given', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/checkout');

    $response->assertRedirect(route('templates.index'));
});

test('checkout page renders a template item without requiring a product', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create([
        'is_active' => true,
        'price' => 150000,
    ]);

    $response = $this->actingAs($user)->get("/checkout?template={$template->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Checkout/Index')
        ->where('item.type', 'template')
        ->where('item.id', $template->id)
        ->where('item.slug', $template->slug)
        ->where('item.price', '150000.00')
    );
});

test('checkout page renders a product item without requiring a template', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'is_active' => true,
        'slug' => 'custom_domain',
        'price' => 49000,
    ]);

    $response = $this->actingAs($user)->get("/checkout?product={$product->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Checkout/Index')
        ->where('item.type', 'product')
        ->where('item.id', $product->id)
        ->where('item.slug', $product->slug)
    );
});

test('store creates an order for a template only', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true, 'price' => 150000]);

    $this->mock(PaymentService::class)
        ->shouldReceive('createTransaction')
        ->andReturn(Payment::make(['payment_url' => 'https://pay.example.com/checkout']));

    $response = $this->actingAs($user)->postJson('/checkout', [
        'template_id' => $template->id,
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $order = Order::where('user_id', $user->id)->first();
    expect($order)->not->toBeNull()
        ->and($order->total_amount)->toBe('150000.00')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->item_type)->toBe('template')
        ->and($order->items->first()->item_id)->toBe($template->id)
        ->and($order->metadata['template_slug'])->toBe($template->slug);
});

test('store creates an order for a product only', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'is_active' => true,
        'type' => 'addon',
        'slug' => 'custom_domain',
        'price' => 49000,
    ]);

    $this->mock(PaymentService::class)
        ->shouldReceive('createTransaction')
        ->andReturn(Payment::make(['payment_url' => 'https://pay.example.com/checkout']));

    $response = $this->actingAs($user)->postJson('/checkout', [
        'product_id' => $product->id,
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $order = Order::where('user_id', $user->id)->first();
    expect($order)->not->toBeNull()
        ->and($order->total_amount)->toBe('49000.00')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->item_type)->toBe('product')
        ->and($order->items->first()->product_id)->toBe($product->id)
        ->and($order->metadata['product_slug'])->toBe($product->slug);
});

test('store rejects order without any item', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/checkout', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['template_id']);
});

test('store rejects inactive template', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => false]);

    $response = $this->actingAs($user)->postJson('/checkout', [
        'template_id' => $template->id,
    ]);

    $response->assertStatus(422);
});

test('store rejects base package product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->base()->create(['is_active' => true]);

    $response = $this->actingAs($user)->postJson('/checkout', [
        'product_id' => $product->id,
    ]);

    $response->assertStatus(422);
});

test('checkout page requires verified email', function () {
    $user = User::factory()->unverified()->create();
    $template = Template::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->get("/checkout?template={$template->slug}");

    $response->assertRedirect('/email/verify');
});
