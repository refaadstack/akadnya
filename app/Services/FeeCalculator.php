<?php

namespace App\Services;

use App\Models\SiteSetting;

class FeeCalculator
{
    /**
     * Calculate the administrative fees (payment gateway fee + tax) borne
     * by the buyer for a given subtotal. Rates are read from the site
     * settings (managed in the Filament admin panel), falling back to
     * the configured defaults.
     *
     * @return array{subtotal: float, payment_gateway_fee: float, tax_amount: float, total_amount: float}
     */
    public function calculate(float $subtotal, ?string $paymentMethod = null): array
    {
        if ($subtotal <= 0) {
            return [
                'subtotal' => 0.0,
                'payment_gateway_fee' => 0.0,
                'tax_amount' => 0.0,
                'total_amount' => 0.0,
            ];
        }

        $config = config('fees.payment_gateway');
        $rule = $config['method_rules'][$paymentMethod]
            ?? SiteSetting::get('payment_gateway_fee_default_rule', $config['default_rule']);

        $paymentGatewayFee = $rule === 'flat'
            ? (float) SiteSetting::get('payment_gateway_fee_flat', $config['flat'])
            : round($subtotal * (float) SiteSetting::get('payment_gateway_fee_percentage', $config['percentage']) / 100, 2);

        $taxEnabled = filter_var(SiteSetting::get('tax_enabled', config('fees.tax.enabled')), FILTER_VALIDATE_BOOLEAN);
        $taxAmount = $taxEnabled
            ? round(($subtotal + $paymentGatewayFee) * (float) SiteSetting::get('tax_rate', config('fees.tax.rate')) / 100, 2)
            : 0.0;

        return [
            'subtotal' => round($subtotal, 2),
            'payment_gateway_fee' => $paymentGatewayFee,
            'tax_amount' => $taxAmount,
            'total_amount' => round($subtotal + $paymentGatewayFee + $taxAmount, 2),
        ];
    }
}