<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Template;
use App\Services\DataContractBuilder;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class WelcomeController extends Controller
{
    public function __construct(private DataContractBuilder $dataContractBuilder) {}

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

        // Guest book add-on promo data.
        $guestBook = Product::query()
            ->addons()
            ->where('slug', 'guest_book')
            ->where('is_active', true)
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
            'guestBook' => $guestBook ? [
                'name' => $guestBook->name,
                'price' => (float) $guestBook->price,
                'original_price' => $guestBook->original_price !== null ? (float) $guestBook->original_price : null,
                'discount_percent' => $guestBook->discount_percent,
                'url' => route('products.index'),
                'demo_qr_svg' => $this->dataContractBuilder->buildDemoGuestQrSvg(),
            ] : null,
        ]);
    }
}
