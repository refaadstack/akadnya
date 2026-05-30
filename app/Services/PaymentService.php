<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentService
{
    public function __construct()
    {
        // Configure Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Request Snap token from Midtrans
     */
    public function requestSnapToken(Order $order): string
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'item_details' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'price' => (int) $item->price,
                    'quantity' => $item->quantity,
                    'name' => $item->name,
                ];
            })->toArray(),
        ];

        $snapToken = Snap::getSnapToken($params);

        // Store payment record
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_transaction_id' => $order->order_number,
            'amount' => $order->total_amount,
            'status' => 'pending',
            'snap_token' => $snapToken,
        ]);

        return $snapToken;
    }

    /**
     * Handle Midtrans webhook notification
     */
    public function handleWebhook(array $notification): void
    {
        $orderNumber = $notification['order_id'];
        $transactionStatus = $notification['transaction_status'];
        $fraudStatus = $notification['fraud_status'] ?? null;

        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $payment = Payment::where('order_id', $order->id)
            ->where('provider_transaction_id', $orderNumber)
            ->firstOrFail();

        // Verify signature
        $this->verifySignature($notification);

        // Update payment based on transaction status
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $this->updatePaymentStatus($payment, $order, 'paid');
            }
        } elseif ($transactionStatus == 'settlement') {
            $this->updatePaymentStatus($payment, $order, 'paid');
        } elseif ($transactionStatus == 'pending') {
            $this->updatePaymentStatus($payment, $order, 'pending');
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $this->updatePaymentStatus($payment, $order, 'failed');
        }
    }

    /**
     * Verify Midtrans signature
     */
    protected function verifySignature(array $notification): void
    {
        // Skip signature verification for simulated payments (dev only)
        if (isset($notification['signature_key']) && $notification['signature_key'] === 'simulated') {
            if (! app()->environment('local')) {
                throw new \Exception('Simulated payments are only allowed in local environment');
            }

            return;
        }

        $serverKey = config('services.midtrans.server_key');
        $orderId = $notification['order_id'];
        $statusCode = $notification['status_code'];
        $grossAmount = $notification['gross_amount'];
        $signatureKey = $notification['signature_key'];

        $mySignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($signatureKey !== $mySignature) {
            throw new \Exception('Invalid signature');
        }
    }

    /**
     * Update payment and order status
     */
    protected function updatePaymentStatus(Payment $payment, Order $order, string $status): void
    {
        $payment->update([
            'status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);

        // Update order status and activate features if paid
        $orderService = app(OrderService::class);
        $orderService->updateOrderStatus($order, $status);
    }
}
