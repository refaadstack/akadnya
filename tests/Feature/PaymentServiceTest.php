<?php

use App\Models\Payment;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->template = Template::factory()->create(['price' => 100000]);
    $this->basePackage = Product::factory()->create([
        'type' => 'base_package',
        'slug' => 'base',
        'price' => 50000,
    ]);

    $orderService = app(OrderService::class);
    $this->order = $orderService->createOrder(
        $this->user,
        $this->template,
        $this->basePackage,
    );

    $this->payment = Payment::create([
        'order_id' => $this->order->id,
        'provider' => 'midtrans',
        'provider_transaction_id' => 'txn-12345',
        'payment_url' => 'https://payment.example.com/pay',
        'amount' => $this->order->total_amount,
        'status' => 'pending',
        'created_at' => now(),
    ]);
});

test('handleWebhook processes settlement notification', function () {
    $notification = [
        'transaction_status' => 'settlement',
        'order_id' => 'txn-12345',
        'transaction_id' => 'txn-12345',
        'gross_amount' => (string) $this->order->total_amount,
        'payment_type' => 'credit_card',
        'status_code' => '200',
    ];

    $paymentService = app(PaymentService::class);
    $paymentService->handleWebhook($notification);

    $this->payment->refresh();
    $this->order->refresh();

    expect($this->payment->status)->toBe('paid')
        ->and($this->payment->paid_at)->not->toBeNull()
        ->and($this->order->status)->toBe('paid');
});

test('handleWebhook processes deny notification', function () {
    $notification = [
        'transaction_status' => 'deny',
        'order_id' => 'txn-12345',
        'transaction_id' => 'txn-12345',
        'status_code' => '202',
    ];

    $paymentService = app(PaymentService::class);
    $paymentService->handleWebhook($notification);

    $this->payment->refresh();
    $this->order->refresh();

    expect($this->payment->status)->toBe('failed')
        ->and($this->payment->paid_at)->toBeNull()
        ->and($this->order->status)->toBe('failed');
});

test('handleWebhook processes expire notification', function () {
    $notification = [
        'transaction_status' => 'expire',
        'order_id' => 'txn-12345',
        'transaction_id' => 'txn-12345',
        'status_code' => '407',
    ];

    $paymentService = app(PaymentService::class);
    $paymentService->handleWebhook($notification);

    $this->payment->refresh();
    $this->order->refresh();

    expect($this->payment->status)->toBe('failed')
        ->and($this->payment->paid_at)->toBeNull()
        ->and($this->order->status)->toBe('failed');
});

test('handleWebhook throws when transaction_id missing', function () {
    $notification = [
        'transaction_status' => 'settlement',
    ];

    $paymentService = app(PaymentService::class);
    $paymentService->handleWebhook($notification);
})->throws(InvalidArgumentException::class);

test('handleWebhook throws when payment not found', function () {
    $notification = [
        'transaction_status' => 'settlement',
        'transaction_id' => 'nonexistent-txn',
    ];

    $paymentService = app(PaymentService::class);
    $paymentService->handleWebhook($notification);
})->throws(RuntimeException::class);
