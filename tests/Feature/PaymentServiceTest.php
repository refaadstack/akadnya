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
        'provider' => 'payment_service',
        'provider_transaction_id' => 'txn-12345',
        'payment_url' => 'https://payment.example.com/pay',
        'amount' => $this->order->total_amount,
        'status' => 'pending',
        'created_at' => now(),
    ]);
});

test('handlePaymentServiceCallback marks order and payment as paid', function () {
    $payload = [
        'transaction_number' => 'txn-12345',
        'product_order_id' => $this->order->order_number,
        'status' => 'paid',
        'payment_method' => 'credit_card',
    ];

    app(PaymentService::class)->handlePaymentServiceCallback($payload);

    $this->payment->refresh();
    $this->order->refresh();

    expect($this->payment->status)->toBe('paid')
        ->and($this->payment->paid_at)->not->toBeNull()
        ->and($this->payment->payment_method)->toBe('credit_card')
        ->and($this->order->status)->toBe('paid');
});

test('handlePaymentServiceCallback marks order and payment as failed', function () {
    $payload = [
        'transaction_number' => 'txn-12345',
        'product_order_id' => $this->order->order_number,
        'status' => 'failed',
    ];

    app(PaymentService::class)->handlePaymentServiceCallback($payload);

    $this->payment->refresh();
    $this->order->refresh();

    expect($this->payment->status)->toBe('failed')
        ->and($this->payment->paid_at)->toBeNull()
        ->and($this->order->status)->toBe('failed');
});

test('handlePaymentServiceCallback is idempotent when status is unchanged', function () {
    $this->payment->update([
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $payload = [
        'transaction_number' => 'txn-12345',
        'product_order_id' => $this->order->order_number,
        'status' => 'paid',
    ];

    app(PaymentService::class)->handlePaymentServiceCallback($payload);

    $this->payment->refresh();

    expect($this->payment->status)->toBe('paid')
        ->and($this->payment->paid_at)->not->toBeNull();
});

test('handlePaymentServiceCallback throws when transaction number missing', function () {
    $payload = [
        'product_order_id' => $this->order->order_number,
        'status' => 'paid',
    ];

    app(PaymentService::class)->handlePaymentServiceCallback($payload);
})->throws(\ErrorException::class);

test('handlePaymentServiceCallback throws when payment not found', function () {
    $payload = [
        'transaction_number' => 'nonexistent-txn',
        'product_order_id' => $this->order->order_number,
        'status' => 'paid',
    ];

    app(PaymentService::class)->handlePaymentServiceCallback($payload);
})->throws(RuntimeException::class);
