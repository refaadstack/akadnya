<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentSimulatorController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Show payment simulator page
     */
    public function index()
    {
        // Get recent pending orders
        $orders = Order::with(['user', 'items', 'payment'])
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        return inertia('Dev/PaymentSimulator', [
            'orders' => $orders->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'user_name' => $order->user->name,
                'user_email' => $order->user->email,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->name,
                    'price' => $item->price,
                ]),
                'payment' => $order->payment ? [
                    'provider_transaction_id' => $order->payment->provider_transaction_id,
                    'status' => $order->payment->status,
                ] : null,
            ]),
        ]);
    }

    /**
     * Simulate payment success
     */
    public function simulateSuccess(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('payment')->findOrFail($request->order_id);

        if (! $order->payment) {
            return back()->with('error', 'Order tidak memiliki payment record');
        }

        // Simulate Midtrans webhook notification for successful payment
        $notification = [
            'transaction_status' => 'settlement',
            'order_id' => $order->payment->provider_transaction_id,
            'gross_amount' => (string) $order->total_amount,
            'payment_type' => 'credit_card',
            'transaction_time' => now()->toIso8601String(),
            'transaction_id' => $order->payment->provider_transaction_id,
            'status_code' => '200',
            'signature_key' => 'simulated',
        ];

        // Process the webhook
        $this->paymentService->handleWebhook($notification);

        return back()->with('success', 'Payment berhasil disimulasikan! Order #'.$order->order_number.' sudah dibayar.');
    }

    /**
     * Simulate payment failure
     */
    public function simulateFailure(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('payment')->findOrFail($request->order_id);

        if (! $order->payment) {
            return back()->with('error', 'Order tidak memiliki payment record');
        }

        // Simulate Midtrans webhook notification for failed payment
        $notification = [
            'transaction_status' => 'deny',
            'order_id' => $order->payment->provider_transaction_id,
            'gross_amount' => (string) $order->total_amount,
            'payment_type' => 'credit_card',
            'transaction_time' => now()->toIso8601String(),
            'transaction_id' => $order->payment->provider_transaction_id,
            'status_code' => '202',
            'signature_key' => 'simulated',
        ];

        // Process the webhook
        $this->paymentService->handleWebhook($notification);

        return back()->with('success', 'Payment failure berhasil disimulasikan! Order #'.$order->order_number.' gagal dibayar.');
    }

    /**
     * Simulate payment expiration
     */
    public function simulateExpired(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('payment')->findOrFail($request->order_id);

        if (! $order->payment) {
            return back()->with('error', 'Order tidak memiliki payment record');
        }

        // Simulate Midtrans webhook notification for expired payment
        $notification = [
            'transaction_status' => 'expire',
            'order_id' => $order->payment->provider_transaction_id,
            'gross_amount' => (string) $order->total_amount,
            'payment_type' => 'credit_card',
            'transaction_time' => now()->toIso8601String(),
            'transaction_id' => $order->payment->provider_transaction_id,
            'status_code' => '407',
            'signature_key' => 'simulated',
        ];

        // Process the webhook
        $this->paymentService->handleWebhook($notification);

        return back()->with('success', 'Payment expiration berhasil disimulasikan! Order #'.$order->order_number.' sudah expired.');
    }
}
