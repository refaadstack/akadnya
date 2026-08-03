<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
    ) {}

    /**
     * Show checkout page with the user's cart items.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $cart = $this->cartService->forPage($request->user());

        if ($cart['items']->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return Inertia::render('Checkout/Index', $cart);
    }

    /**
     * Create an order from the user's cart and redirect to payment.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $cartItems = $user->cartItems()->get();
        $order = null;

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang belanja kosong.',
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($user, $cartItems) {
                return $this->orderService->createOrderFromCart($user, $cartItems);
            });

            $paymentService = app(PaymentService::class);
            $payment = $paymentService->createTransaction($order);

            $this->cartService->clear($user);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_url' => $payment->payment_url,
                'total_amount' => $order->total_amount,
            ]);
        } catch (\Throwable $e) {
            if ($order) {
                $order->forceFill(['status' => 'failed'])->save();
            }

            Log::error('Checkout store error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $user->id,
                'cart_item_count' => $cartItems->count(),
                'order_id' => $order?->id,
                'order_number' => $order?->order_number,
                'payment_service_url' => config('services.payment_service.base_url'),
                'payment_service_key_configured' => filled(config('services.payment_service.product_key')),
            ]);

            return response()->json([
                'success' => false,
                'message' => app()->isLocal()
                    ? $e->getMessage()
                    : 'Terjadi kesalahan saat membuat order. Silakan coba lagi.',
            ], 500);
        }
    }
}
