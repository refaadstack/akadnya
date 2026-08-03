<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display all active products available à la carte.
     */
    public function index(): Response
    {
        $products = Product::active()
            ->orderByRaw("FIELD(type, 'base_package', 'addon')")
            ->orderBy('price')
            ->get()
            ->map(fn ($product) => [
                'id' => $product->id,
                'type' => $product->type,
                'slug' => $product->slug,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'is_recurring' => $product->is_recurring ?? false,
                'recurring_interval' => $product->recurring_interval,
            ]);

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }
}
