<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class WelcomeController extends Controller
{
    /**
     * Show welcome page
     */
    public function index(): Response
    {
        // Get base package product
        $basePackage = Product::where('type', 'base_package')
            ->where('is_active', true)
            ->first();

        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'basePackage' => $basePackage ? [
                'name' => $basePackage->name,
                'price' => $basePackage->price,
                'description' => $basePackage->description,
            ] : null,
        ]);
    }
}
