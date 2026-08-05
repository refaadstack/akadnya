<?php

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cart page requires authentication', function () {
    $this->get('/keranjang')->assertRedirect('/login');
});

test('adding to cart requires authentication', function () {
    $template = Template::factory()->create(['is_active' => true]);

    $this->post('/keranjang', [
        'item_type' => 'template',
        'item_id' => $template->id,
    ])->assertRedirect('/login');
});

test('user can add a template to cart', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create([
        'is_active' => true,
        'price' => 150000,
        'original_price' => 199000,
    ]);

    $this->actingAs($user)->post('/keranjang', [
        'item_type' => 'template',
        'item_id' => $template->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'item_type' => 'template',
        'item_id' => $template->id,
        'quantity' => 1,
    ]);
});

test('adding the same product again increments its quantity', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($user)->post('/keranjang', [
        'item_type' => 'product',
        'item_id' => $product->id,
    ]);
    $this->actingAs($user)->post('/keranjang', [
        'item_type' => 'product',
        'item_id' => $product->id,
    ]);

    expect(CartItem::where('user_id', $user->id)->first()->quantity)->toBe(2);
    expect(CartItem::where('user_id', $user->id)->count())->toBe(1);
});

test('adding the same template again updates its preview data', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true]);

    $this->actingAs($user)->post('/keranjang', [
        'item_type' => 'template',
        'item_id' => $template->id,
        'preview_data' => ['bride_name' => 'Sari'],
    ]);
    $this->actingAs($user)->post('/keranjang', [
        'item_type' => 'template',
        'item_id' => $template->id,
        'preview_data' => ['bride_name' => 'Dewi'],
    ]);

    $item = CartItem::where('user_id', $user->id)->first();
    expect($item->preview_data)->toBe(['bride_name' => 'Dewi']);
    expect($item->quantity)->toBe(1);
    expect(CartItem::where('user_id', $user->id)->count())->toBe(1);
});

test('cart rejects inactive template', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => false]);

    $this->actingAs($user)
        ->from('/templates')
        ->post('/keranjang', [
            'item_type' => 'template',
            'item_id' => $template->id,
        ])
        ->assertSessionHasErrors('item_id');

    $this->assertDatabaseCount('cart_items', 0);
});

test('cart rejects base package product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->base()->create(['is_active' => true]);

    $this->actingAs($user)
        ->from('/produk')
        ->post('/keranjang', [
            'item_type' => 'product',
            'item_id' => $product->id,
        ])
        ->assertSessionHasErrors('item_id');

    $this->assertDatabaseCount('cart_items', 0);
});

test('cart rejects invalid item type', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/')
        ->post('/keranjang', [
            'item_type' => 'package',
            'item_id' => 1,
        ])
        ->assertSessionHasErrors('item_type');

    $this->assertDatabaseCount('cart_items', 0);
});

test('user can update product quantity in cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);
    $item = CartItem::create([
        'user_id' => $user->id,
        'item_type' => 'product',
        'item_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)->patchJson("/keranjang/{$item->id}", [
        'quantity' => 5,
    ])->assertOk()->assertJson(['success' => true, 'quantity' => 5]);

    expect($item->fresh()->quantity)->toBe(5);
});

test('template quantity in cart cannot be changed', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true]);
    $item = CartItem::create([
        'user_id' => $user->id,
        'item_type' => 'template',
        'item_id' => $template->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)->patchJson("/keranjang/{$item->id}", [
        'quantity' => 3,
    ])->assertStatus(422);
});

test('user can remove a cart item', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true]);
    $item = CartItem::create([
        'user_id' => $user->id,
        'item_type' => 'template',
        'item_id' => $template->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)->delete("/keranjang/{$item->id}")->assertRedirect();

    $this->assertDatabaseCount('cart_items', 0);
});

test('user can clear the cart', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['is_active' => true]);

    CartItem::create(['user_id' => $user->id, 'item_type' => 'template', 'item_id' => $template->id]);
    CartItem::create(['user_id' => $user->id, 'item_type' => 'product', 'item_id' => $product->id]);

    $this->actingAs($user)->delete('/keranjang')->assertRedirect();

    $this->assertDatabaseCount('cart_items', 0);
});

test('cart is isolated per user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $template = Template::factory()->create(['is_active' => true]);

    $this->actingAs($userA)->post('/keranjang', [
        'item_type' => 'template',
        'item_id' => $template->id,
    ]);

    $this->actingAs($userB)->get('/keranjang')
        ->assertInertia(fn ($page) => $page
            ->component('Cart/Index')
            ->has('items', 0)
        );
});

test('cart page shows items with discounted prices and totals', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create([
        'is_active' => true,
        'price' => 150000,
        'original_price' => 199000,
    ]);
    $product = Product::factory()->create([
        'is_active' => true,
        'price' => 19000,
        'original_price' => 25000,
    ]);

    CartItem::create(['user_id' => $user->id, 'item_type' => 'template', 'item_id' => $template->id, 'quantity' => 1]);
    CartItem::create(['user_id' => $user->id, 'item_type' => 'product', 'item_id' => $product->id, 'quantity' => 2]);

    $response = $this->actingAs($user)->get('/keranjang');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Cart/Index')
        ->has('items', 2)
        ->where('items.0.name', $template->name)
        ->where('items.0.original_price', 199000)
        ->where('items.1.name', $product->name)
        ->where('items.1.quantity', 2)
        ->where('totals.subtotal', 188000)
        ->where('totals.original_subtotal', 249000)
        ->where('totals.savings', 61000)
    );
});

test('cart page requires verified email', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get('/keranjang')->assertRedirect('/email/verify');
});
