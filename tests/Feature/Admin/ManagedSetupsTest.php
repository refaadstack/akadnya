<?php

use App\Filament\Pages\ManagedSetups;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserFeature;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

function makeOrderItem(): OrderItem
{
    $order = Order::create([
        'user_id' => User::factory()->create()->id,
        'order_number' => 'ORD-TEST-'.uniqid(),
        'status' => 'paid',
        'total_amount' => 79000,
    ]);

    $product = Product::factory()->create([
        'type' => 'addon',
        'slug' => 'managed_setup',
    ]);

    return OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'item_type' => 'product',
        'item_id' => $product->id,
        'name' => $product->name,
        'price' => $product->price,
        'quantity' => 1,
    ]);
}

test('admin can open the managed setups page', function () {
    $this->actingAs($this->admin)
        ->get('/admin/managed-setups')
        ->assertOk();
});

test('non-admin cannot open the managed setups page', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get('/admin/managed-setups')
        ->assertForbidden();
});

test('managed setup records default to pending status', function () {
    $user = User::factory()->create();
    $orderItem = makeOrderItem();

    $feature = UserFeature::create([
        'user_id' => $user->id,
        'feature' => 'managed_setup',
        'order_item_id' => $orderItem->id,
        'activated_at' => now(),
        'metadata' => ['order_number' => $orderItem->order->order_number],
    ]);

    expect($feature->setup_status)->toBe('pending');
});

test('admin can update setup status and notes', function () {
    $user = User::factory()->create();
    $orderItem = makeOrderItem();

    $feature = UserFeature::create([
        'user_id' => $user->id,
        'feature' => 'managed_setup',
        'order_item_id' => $orderItem->id,
        'activated_at' => now(),
    ]);

    Livewire::actingAs($this->admin)
        ->test(ManagedSetups::class)
        ->callTableAction('manageSetup', $feature->getKey(), [
            'status' => 'in_progress',
            'notes' => 'Menunggu data tamu dari user',
        ])
        ->assertHasNoErrors();

    $feature->refresh();

    expect($feature->setup_status)->toBe('in_progress');
    expect($feature->setup_notes)->toBe('Menunggu data tamu dari user');
    expect($feature->setup_updated_at)->not->toBeNull();
});
