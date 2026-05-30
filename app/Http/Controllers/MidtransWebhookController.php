<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Handle Midtrans webhook notification
     */
    public function handle(Request $request)
    {
        try {
            $notification = $request->all();

            Log::info('Midtrans webhook received', $notification);

            // Handle the webhook
            $this->paymentService->handleWebhook($notification);

            return response()->json([
                'success' => true,
                'message' => 'Notification processed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error: '.$e->getMessage(), [
                'notification' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
