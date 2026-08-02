<?php

use App\Http\Controllers\PaymentServiceCallbackController;
use Illuminate\Support\Facades\Route;

Route::post('/payment/callback', [PaymentServiceCallbackController::class, 'handle'])
    ->name('payment-service.callback');
