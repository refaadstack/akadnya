<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checkout page requires authentication', function () {
    $this->get('/checkout')->assertRedirect('/login');
});

test('checkout page requires verified email', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get('/checkout')->assertRedirect('/email/verify');
});

test('checkout page redirects to cart when cart is empty', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/checkout')->assertRedirect(route('cart.index'));
});

test('checkout page renders all cart items', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create([
        'is_active' => true,
        'price' => 150000,
    ]);
    $product = Product::factory()->create([
        'is_active' => true,
        'price' => 49000,
    ]);

    CartItem::create(['user_id' => $user->id, 'item_type' => 'template', 'item_id' => $template->id]);
    CartItem::create(['user_id' => $user->id, 'item_type' => 'product', 'item_id' => $product->id, 'quantity' => 2]);

    $response = $this->actingAs($user)->get('/checkout');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Checkout/Index')
        ->has('items', 2)
        ->where('items.0.type', 'template')
        ->where('items.0.name', $template->name)
        ->where('items.1.type', 'product')
        ->where('items.1.quantity', 2)
        ->where('totals.subtotal', 248000)
        ->where('totals.payment_gateway_fee', 4960)
        ->where('totals.tax_amount', 0)
        ->where('totals.grand_total', 252960)
    );
});

test('store creates one order with all cart items and clears the cart', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true, 'price' => 150000]);
    $product = Product::factory()->create(['is_active' => true, 'type' => 'addon', 'price' => 19000]);

    CartItem::create([
        'user_id' => $user->id,
        'item_type' => 'template',
        'item_id' => $template->id,
        'preview_data' => ['bride_name' => 'Sari'],
    ]);
    CartItem::create([
        'user_id' => $user->id,
        'item_type' => 'product',
        'item_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->mock(PaymentService::class)
        ->shouldReceive('createTransaction')
        ->andReturn(Payment::make(['payment_url' => 'https://pay.example.com/checkout']));

    $response = $this->actingAs($user)->postJson('/checkout', []);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $order = Order::where('user_id', $user->id)->first();
    expect($order)->not->toBeNull()
        ->and($order->subtotal_amount)->toBe('188000.00')
        ->and($order->payment_gateway_fee)->toBe('3760.00')
        ->and($order->tax_amount)->toBe('0.00')
        ->and($order->total_amount)->toBe('191760.00')
        ->and($order->items)->toHaveCount(2)
        ->and($order->items->firstWhere('item_type', 'template')->item_id)->toBe($template->id)
        ->and($order->items->firstWhere('item_type', 'product')->quantity)->toBe(2)
        ->and($order->metadata['preview_data'][$template->slug])->toBe(['bride_name' => 'Sari'])
        ->and($order->metadata['template_slugs'])->toBe([$template->slug])
        ->and($order->metadata['product_slugs'])->toBe([$product->slug]);

    $this->assertDatabaseCount('cart_items', 0);
});

test('store rejects an empty cart', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/checkout', []);

    $response->assertStatus(422);
    $response->assertJson(['success' => false]);
});

test('store rejects cart items that are no longer purchasable', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => false]);

    CartItem::create([
        'user_id' => $user->id,
        'item_type' => 'template',
        'item_id' => $template->id,
    ]);

    $response = $this->actingAs($user)->postJson('/checkout', []);

    $response->assertStatus(500);
    $this->assertDatabaseCount('orders', 0);
});
