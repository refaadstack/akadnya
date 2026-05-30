<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Template;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Show checkout page
     */
    public function index(Request $request): Response
    {
        $templateSlug = $request->query('template');

        if (! $templateSlug) {
            abort(400, 'Template parameter is required');
        }

        $template = Template::where('slug', $templateSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Get all base package options
        $basePackages = Product::where('type', 'base_package')
            ->where('is_active', true)
            ->orderBy('price')
            ->get()
            ->map(fn ($product) => [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'is_recurring' => $product->is_recurring ?? false,
                'recurring_interval' => $product->recurring_interval,
            ]);

        // Get add-on products
        $addons = Product::where('type', 'addon')
            ->where('is_active', true)
            ->orderBy('price')
            ->get()
            ->map(fn ($product) => [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'is_recurring' => $product->is_recurring ?? false,
                'recurring_interval' => $product->recurring_interval,
            ]);

        return Inertia::render('Checkout/Index', [
            'template' => [
                'id' => $template->id,
                'slug' => $template->slug,
                'name' => $template->name,
                'price' => $template->price,
            ],
            'basePackages' => $basePackages,
            'addons' => $addons,
        ]);
    }

    /**
     * Store order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'base_package_id' => 'required|exists:products,id',
            'addon_ids' => 'nullable|array',
            'addon_ids.*' => 'exists:products,id',
            'preview_data' => 'nullable|array',
        ]);

        $template = Template::findOrFail($validated['template_id']);
        $basePackage = Product::findOrFail($validated['base_package_id']);
        $addonIds = $validated['addon_ids'] ?? [];
        $previewData = $validated['preview_data'] ?? null;

        try {
            // Create order
            $orderService = app(OrderService::class);
            $order = $orderService->createOrder(
                $request->user(),
                $template,
                $basePackage,
                $addonIds,
                $previewData
            );

            // Get Midtrans Snap token
            $paymentService = app(PaymentService::class);
            $snapToken = $paymentService->requestSnapToken($order);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'snap_token' => $snapToken,
                'total_amount' => $order->total_amount,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Checkout store error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $request->user()->id,
                'template_id' => $validated['template_id'],
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
