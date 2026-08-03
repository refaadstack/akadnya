<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display all active add-on products available à la carte.
     *
     * Templates include full access, so only add-ons (e.g. guest book,
     * custom domain, managed setup) are sold as standalone products.
     */
    public function index(): Response
    {
        $products = Product::addons()
            ->active()
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
