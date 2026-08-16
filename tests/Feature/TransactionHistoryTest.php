<?php

use App\Models\Payment;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Services\OrderService;
use Inertia\Testing\AssertableInertia as Assert;

test('unauthenticated users are redirected from the transactions page', function () {
    $this->get(route('dashboard.transactions'))
        ->assertRedirect(route('login'));
});

test('transactions page shows only the authenticated user orders', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $template = Template::factory()->create(['price' => 100000]);
    $basePackage = Product::factory()->create([
        'type' => 'base_package',
        'slug' => 'base',
        'price' => 50000,
    ]);

    $order = app(OrderService::class)->createOrder($user, $template, $basePackage);
    $order->update([
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    Payment::create([
        'order_id' => $order->id,
        'provider' => 'payment_service',
        'provider_transaction_id' => 'PS-20260816-XYZ789',
        'payment_url' => 'https://pay.example.com/pay/xyz',
        'payment_method' => 'credit_card',
        'amount' => $order->total_amount,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $otherOrder = app(OrderService::class)->createOrder($otherUser, $template, $basePackage);

    $this->actingAs($user)
        ->get(route('dashboard.transactions'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Transactions')
            ->has('orders', 1)
            ->where('orders.0.order_number', $order->order_number)
            ->where('orders.0.status', 'paid')
            ->where('orders.0.total_amount', (int) $order->total_amount)
            ->where('orders.0.payment.status', 'paid')
            ->where('orders.0.payment.payment_method', 'credit_card')
            ->where('orders.0.items.0.name', $template->name)
            ->where('orders.0.items.1.name', $basePackage->name));

    expect($otherOrder->user_id)->toBe($otherUser->id);
});

test('transactions page renders empty state for users without orders', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.transactions'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Transactions')
            ->has('orders', 0));
});
