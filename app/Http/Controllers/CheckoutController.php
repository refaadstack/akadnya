<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Template;
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
    /**
     * Show checkout page for a single item (template or product, à la carte).
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $templateSlug = $request->query('template');
        $productSlug = $request->query('product');

        if ($templateSlug) {
            $template = Template::where('slug', $templateSlug)
                ->where('is_active', true)
                ->firstOrFail();

            return Inertia::render('Checkout/Index', [
                'item' => [
                    'type' => 'template',
                    'id' => $template->id,
                    'slug' => $template->slug,
                    'name' => $template->name,
                    'description' => 'Template undangan digital',
                    'price' => $template->price,
                    'is_free' => $template->is_free,
                ],
            ]);
        }

        if ($productSlug) {
            $product = Product::where('slug', $productSlug)
                ->where('type', 'addon')
                ->where('is_active', true)
                ->firstOrFail();

            return Inertia::render('Checkout/Index', [
                'item' => [
                    'type' => 'product',
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'is_recurring' => $product->is_recurring ?? false,
                    'recurring_interval' => $product->recurring_interval,
                ],
            ]);
        }

        return redirect()->route('templates.index');
    }

    /**
     * Store order
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template_id' => 'nullable|required_without:product_id|exists:templates,id,is_active,1',
            'product_id' => 'nullable|required_without:template_id|exists:products,id,type,addon,is_active,1',
            'preview_data' => 'nullable|array',
        ]);

        $template = $validated['template_id'] ?? null ? Template::find($validated['template_id']) : null;
        $product = $validated['product_id'] ?? null ? Product::find($validated['product_id']) : null;
        $previewData = $validated['preview_data'] ?? null;
        $order = null;

        try {
            $order = DB::transaction(function () use ($request, $template, $product, $previewData) {
                $orderService = app(OrderService::class);

                return $orderService->createOrder(
                    $request->user(),
                    $template,
                    $product,
                    $previewData
                );
            });

            $paymentService = app(PaymentService::class);
            $payment = $paymentService->createTransaction($order);

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
                'user_id' => $request->user()->id,
                'template_id' => $template?->id,
                'product_id' => $product?->id,
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
