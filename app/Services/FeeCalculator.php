<?php

namespace App\Services;

class FeeCalculator
{
    /**
     * Calculate the administrative fees (payment gateway fee + tax) borne
     * by the buyer for a given subtotal.
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
        $rule = $config['method_rules'][$paymentMethod] ?? $config['default_rule'];

        $paymentGatewayFee = $rule === 'flat'
            ? (float) $config['flat']
            : round($subtotal * (float) $config['percentage'] / 100, 2);

        $taxAmount = round(($subtotal + $paymentGatewayFee) * (float) config('fees.tax.rate') / 100, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'payment_gateway_fee' => $paymentGatewayFee,
            'tax_amount' => $taxAmount,
            'total_amount' => round($subtotal + $paymentGatewayFee + $taxAmount, 2),
        ];
    }
}