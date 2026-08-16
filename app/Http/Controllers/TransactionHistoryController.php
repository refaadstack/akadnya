<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionHistoryController extends Controller
{
    /**
     * Display the authenticated user's transaction history.
     */
    public function index(Request $request): Response
    {
        $orders = $request->user()
            ->orders()
            ->with(['payment', 'items'])
            ->latest()
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_amount' => (float) $order->total_amount,
                'subtotal_amount' => (float) $order->subtotal_amount,
                'payment_gateway_fee' => (float) $order->payment_gateway_fee,
                'tax_amount' => (float) $order->tax_amount,
                'paid_at' => $order->paid_at?->toISOString(),
                'created_at' => $order->created_at?->toISOString(),
                'payment' => $order->payment ? [
                    'status' => $order->payment->status,
                    'payment_method' => $order->payment->payment_method,
                    'provider_transaction_id' => $order->payment->provider_transaction_id,
                    'paid_at' => $order->payment->paid_at?->toISOString(),
                ] : null,
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->name,
                    'price' => (float) $item->price,
                    'quantity' => $item->quantity,
                ])->values(),
            ])
            ->values();

        return Inertia::render('Dashboard/Transactions', [
            'orders' => $orders,
        ]);
    }
}
