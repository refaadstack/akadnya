<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentFinishController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $orderNumber = $request->query('order') ?? $request->query('order_id');

        $order = Order::with('payment')
            ->where('order_number', $orderNumber)
            ->when($request->user(), fn ($query, $user) => $query->where('user_id', $user->id))
            ->first();

        return Inertia::render('Payment/Finish', [
            'order' => $order ? [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment?->status,
                'paid_at' => $order->payment?->paid_at?->toISOString(),
            ] : null,
        ]);
    }
}
