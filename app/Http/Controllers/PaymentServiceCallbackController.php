<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentServiceCallbackController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function handle(Request $request)
    {
        $expected = config('services.payment_service.callback_secret');

        if (filled($expected) && ! hash_equals($expected, (string) $request->header('X-Payment-Callback-Key'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid callback key',
            ], 401);
        }

        try {
            $payload = $request->all();

            Log::info('Payment Service callback received', $payload);

            $this->paymentService->handlePaymentServiceCallback($payload);

            return response()->json([
                'success' => true,
                'message' => 'Payment callback processed',
            ]);
        } catch (\Throwable $e) {
            Log::error('Payment Service callback error: '.$e->getMessage(), [
                'payload' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
