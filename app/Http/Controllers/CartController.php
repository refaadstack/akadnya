<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Show the shopping cart page.
     */
    public function index(Request $request): Response
    {
        $cart = $this->cartService->forPage($request->user());

        return Inertia::render('Cart/Index', $cart);
    }

    /**
     * Add an item to the cart.
     */
    public function store(Request $request): RedirectResponse
    {
        $itemType = $request->input('item_type');
        $existsRule = Rule::exists($itemType === 'template' ? 'templates' : 'products', 'id')
            ->where('is_active', true);

        if ($itemType === 'product') {
            $existsRule->where('type', 'addon');
        }

        $validated = $request->validate([
            'item_type' => ['required', 'in:template,product'],
            'item_id' => ['required', 'integer', $existsRule],
            'preview_data' => 'nullable|array',
        ]);

        try {
            $this->cartService->add(
                $request->user(),
                $validated['item_type'],
                (int) $validated['item_id'],
                $validated['preview_data'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withErrors(['item_id' => $e->getMessage()])
                ->withInput();
        }

        return back()->with('success', 'Ditambahkan ke keranjang.');
    }

    /**
     * Update the quantity of a product cart item.
     */
    public function update(Request $request, int $item): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        try {
            $cartItem = $this->cartService->updateQuantity(
                $request->user(),
                $item,
                (int) $validated['quantity'],
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'quantity' => $cartItem->quantity,
        ]);
    }

    /**
     * Remove a single item from the cart.
     */
    public function destroy(Request $request, int $item): RedirectResponse
    {
        $this->cartService->remove($request->user(), $item);

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    /**
     * Empty the cart.
     */
    public function clear(Request $request): RedirectResponse
    {
        $this->cartService->clear($request->user());

        return back()->with('success', 'Keranjang dikosongkan.');
    }
}
