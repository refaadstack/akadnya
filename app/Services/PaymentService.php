<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function createTransaction(Order $order): Payment
    {
        $baseUrl = config('services.payment_service.base_url');
        $productKey = config('services.payment_service.product_key');

        if (blank($baseUrl) || blank($productKey)) {
            throw new \RuntimeException('Payment service configuration is missing.');
        }

        $items = $order->items->map(function ($item) {
            return [
                'id' => (string) $item->id,
                'name' => $item->name,
                'price' => (int) $item->price,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        if ((float) $order->payment_gateway_fee > 0) {
            $items[] = [
                'id' => 'fee-payment-gateway',
                'name' => 'Biaya Payment Gateway',
                'price' => (int) $order->payment_gateway_fee,
                'quantity' => 1,
            ];
        }

        if ((float) $order->tax_amount > 0) {
            $items[] = [
                'id' => 'fee-tax',
                'name' => 'Pajak (PPN)',
                'price' => (int) $order->tax_amount,
                'quantity' => 1,
            ];
        }

        $payload = [
            'product_order_id' => $order->order_number,
            'amount' => (int) $order->total_amount,
            'items' => $items,
            'customer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'callback_url' => route('payment-service.callback'),
        ];

        Log::info('Requesting payment service transaction', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'amount' => (int) $order->total_amount,
        ]);

        $response = Http::timeout(30)
            ->withHeaders([
                'X-Product-Key' => $productKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($baseUrl.'/api/v1/transactions', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Failed to create transaction: '.$response->body());
        }

        $data = $response->json();

        $paymentUrl = $data['payment_url'];
        if (! str_starts_with($paymentUrl, 'http')) {
            $publicUrl = rtrim(config('services.payment_service.public_url') ?? config('services.payment_service.base_url'), '/');
            $paymentUrl = $publicUrl.'/'.ltrim($paymentUrl, '/');
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'payment_service',
            'provider_transaction_id' => $data['transaction_number'],
            'amount' => $order->total_amount,
            'status' => 'pending',
            'payment_url' => $paymentUrl,
        ]);

        return $payment;
    }

    public function handlePaymentServiceCallback(array $payload): void
    {
        $transactionNumber = $payload['transaction_number'];
        $productOrderId = $payload['product_order_id'];
        $status = $payload['status'];

        $order = \App\Models\Order::where('order_number', $productOrderId)->firstOrFail();
        $payment = Payment::where('provider_transaction_id', $transactionNumber)->firstOrFail();

        if ($payment->status === $status) {
            Log::info('Payment callback idempotent - status unchanged', [
                'transaction_number' => $transactionNumber,
                'status' => $status,
            ]);

            return;
        }

        $payment->update([
            'status' => $status,
            'paid_at' => $status === 'paid' ? now() : $payment->paid_at,
            'payment_method' => $payload['payment_method'] ?? null,
            'raw_response' => $payload['raw'] ?? null,
        ]);

        $orderService = app(OrderService::class);
        $orderService->updateOrderStatus($order, $status);

        Log::info('Payment callback processed', [
            'transaction_number' => $transactionNumber,
            'order_number' => $productOrderId,
            'status' => $status,
        ]);
    }
}
