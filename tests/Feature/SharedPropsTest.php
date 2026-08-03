<?php

use App\Models\Invitation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Models\UserFeature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createUserFeature(User $user, string $feature, array $extra = [], ?Product $product = null): UserFeature
{
    $product ??= Product::factory()->create(['slug' => $feature]);
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.strtoupper(Str::random(8)),
        'status' => 'paid',
        'total_amount' => 19000,
    ]);
    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'item_type' => 'product',
        'item_id' => $product->id,
        'product_id' => $product->id,
        'name' => $product->name,
        'price' => 19000,
        'quantity' => 1,
    ]);

    return UserFeature::create(array_merge([
        'user_id' => $user->id,
        'feature' => $feature,
        'order_item_id' => $orderItem->id,
        'activated_at' => now(),
    ], $extra));
}

test('hasInvitation and features are shared for users with an invitation', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    Invitation::factory()->for($user)->for($template)->create();

    createUserFeature($user, 'guest_book');

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('hasInvitation', true)
            ->where('features', ['guest_book']));
});

test('hasInvitation is false and features empty for users without an invitation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('hasInvitation', false)
            ->where('features', []));
});

test('expired features are not shared', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    Invitation::factory()->for($user)->for($template)->create();

    createUserFeature($user, 'guest_book', [
        'activated_at' => now()->subYear(),
        'expires_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page->where('features', []));
});

test('duplicate features are shared only once', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    Invitation::factory()->for($user)->for($template)->create();

    $product = Product::factory()->create(['slug' => 'guest_book']);
    createUserFeature($user, 'guest_book', [], $product);
    createUserFeature($user, 'guest_book', [], $product);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page->where('features', ['guest_book']));
});
