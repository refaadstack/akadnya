<?php

use App\Models\Template;
use App\Models\User;
use App\Services\OrderService;

test('debug - free order paid', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_free' => false, 'price' => 150000]);

    $order = app(OrderService::class)->createOrder($user, $template, null, null, free: true);
    app(OrderService::class)->updateOrderStatus($order, 'paid', notify: false);

    expect($order->fresh()->status)->toBe('paid');
    expect((float) $order->fresh()->total_amount)->toBe(0.0);
});
