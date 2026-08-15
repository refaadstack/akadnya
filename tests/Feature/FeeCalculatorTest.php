<?php

use App\Models\SiteSetting;
use App\Services\FeeCalculator;

beforeEach(function () {
    SiteSetting::flush();
});

it('calculates percentage gateway fee with tax disabled by default', function () {
    $fees = app(FeeCalculator::class)->calculate(188000);

    expect($fees)->toBe([
        'subtotal' => 188000.0,
        'payment_gateway_fee' => 3760.0,
        'tax_amount' => 0.0,
        'total_amount' => 191760.0,
    ]);
});

it('applies tax when enabled in the site settings', function () {
    SiteSetting::set('tax_enabled', true);

    $fees = app(FeeCalculator::class)->calculate(188000);

    expect($fees['payment_gateway_fee'])->toBe(3760.0)
        ->and($fees['tax_amount'])->toBe(21093.6)
        ->and($fees['total_amount'])->toBe(212853.6);
});

it('uses the site settings rate and percentage overrides', function () {
    SiteSetting::set('tax_enabled', true);
    SiteSetting::set('tax_rate', 12);
    SiteSetting::set('payment_gateway_fee_percentage', 3);

    $fees = app(FeeCalculator::class)->calculate(100000);

    expect($fees['payment_gateway_fee'])->toBe(3000.0)
        ->and($fees['tax_amount'])->toBe(12360.0)
        ->and($fees['total_amount'])->toBe(115360.0);
});

it('uses the flat fee rule when a payment method maps to flat', function () {
    $fees = app(FeeCalculator::class)->calculate(188000, 'bank_transfer');

    expect($fees['payment_gateway_fee'])->toBe(4000.0)
        ->and($fees['tax_amount'])->toBe(0.0)
        ->and($fees['total_amount'])->toBe(192000.0);
});

it('respects the default rule setting', function () {
    SiteSetting::set('payment_gateway_fee_default_rule', 'flat');

    $fees = app(FeeCalculator::class)->calculate(188000);

    expect($fees['payment_gateway_fee'])->toBe(4000.0)
        ->and($fees['total_amount'])->toBe(192000.0);
});

it('rounds fees to two decimals', function () {
    $fees = app(FeeCalculator::class)->calculate(10000);

    expect($fees['payment_gateway_fee'])->toBe(200.0)
        ->and($fees['tax_amount'])->toBe(0.0)
        ->and($fees['total_amount'])->toBe(10200.0);
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