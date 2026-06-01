<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Template;
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

        $featuredTemplates = Template::active()
            ->orderBy('is_free', 'desc')
            ->orderBy('name')
            ->limit(3)
            ->get()
            ->map(fn (Template $template): array => [
                'id' => $template->id,
                'slug' => $template->slug,
                'name' => $template->name,
                'thumbnail_url' => $template->thumbnail_url,
                'price' => $template->price,
                'is_free' => $template->is_free,
            ]);

        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'basePackage' => $basePackage ? [
                'name' => $basePackage->name,
                'price' => $basePackage->price,
                'description' => $basePackage->description,
            ] : null,
            'featuredTemplates' => $featuredTemplates,
        ]);
    }
}
