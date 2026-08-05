<?php

namespace App\Http\Controllers;

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
                'price' => (float) $template->price,
                'original_price' => $template->original_price !== null ? (float) $template->original_price : null,
                'discount_percent' => $template->discount_percent,
                'is_free' => $template->is_free,
            ]);

        // Cheapest paid template as the "starting from" price.
        // Buying a template includes full access (editor, subdomain, publish).
        $startingTemplate = Template::active()
            ->where('is_free', false)
            ->orderBy('price')
            ->first();

        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'startingTemplate' => $startingTemplate ? [
                'name' => $startingTemplate->name,
                'price' => (float) $startingTemplate->price,
                'original_price' => $startingTemplate->original_price !== null ? (float) $startingTemplate->original_price : null,
                'discount_percent' => $startingTemplate->discount_percent,
            ] : null,
            'featuredTemplates' => $featuredTemplates,
        ]);
    }
}
