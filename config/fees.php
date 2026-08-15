<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Administrative Fees (borne by the buyer)
    |--------------------------------------------------------------------------
    |
    | Payment gateway fees follow Midtrans-style pricing: some payment
    | methods charge a percentage of the subtotal, others a flat fee per
    | transaction. The payment method is unknown at checkout, so the
    | estimate uses the default rule; per-method overrides can be added
    | to `method_rules` (keys match the payment_method sent by the
    | payment service callback).
    |
    */

    'payment_gateway' => [
        'percentage' => env('PAYMENT_GATEWAY_FEE_PERCENTAGE', 2.0),
        'flat' => env('PAYMENT_GATEWAY_FEE_FLAT', 4000.0),
        'default_rule' => env('PAYMENT_GATEWAY_FEE_DEFAULT_RULE', 'percentage'),
        'method_rules' => [
            'bank_transfer' => 'flat',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tax (PPN)
    |--------------------------------------------------------------------------
    |
    | Value-added tax applied to the taxable base, which is the subtotal
    | plus the payment gateway fee.
    |
    */

    'tax' => [
        'rate' => env('TAX_RATE_PERCENTAGE', 11.0),
    ],

];