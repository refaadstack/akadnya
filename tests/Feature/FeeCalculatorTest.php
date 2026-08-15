<?php

use App\Services\FeeCalculator;

it('calculates percentage gateway fee with tax on subtotal plus fee', function () {
    $fees = app(FeeCalculator::class)->calculate(188000);

    expect($fees)->toBe([
        'subtotal' => 188000.0,
        'payment_gateway_fee' => 3760.0,
        'tax_amount' => 21093.6,
        'total_amount' => 212853.6,
    ]);
});

it('uses the flat fee rule when a payment method maps to flat', function () {
    $fees = app(FeeCalculator::class)->calculate(188000, 'bank_transfer');

    expect($fees['payment_gateway_fee'])->toBe(4000.0)
        ->and($fees['tax_amount'])->toBe(21120.0)
        ->and($fees['total_amount'])->toBe(213120.0);
});

it('rounds fees to two decimals', function () {
    $fees = app(FeeCalculator::class)->calculate(10000);

    expect($fees['payment_gateway_fee'])->toBe(200.0)
        ->and($fees['tax_amount'])->toBe(1122.0)
        ->and($fees['total_amount'])->toBe(11322.0);
});

it('returns zero fees for a zero subtotal', function () {
    $fees = app(FeeCalculator::class)->calculate(0);

    expect($fees)->toBe([
        'subtotal' => 0.0,
        'payment_gateway_fee' => 0.0,
        'tax_amount' => 0.0,
        'total_amount' => 0.0,
    ]);
});