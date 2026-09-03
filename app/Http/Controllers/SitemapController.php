<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Template;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $baseUrl = config('app.url', 'https://akadnya.com');

        $staticPages = collect([
            '/' => now(),
            '/faq' => now(),
            '/tutorial' => now(),
            '/terms' => now(),
            '/privacy' => now(),
            '/templates' => now(),
            '/produk' => now(),
        ]);

        $templates = Template::where('is_active', true)
            ->get()
            ->mapWithKeys(fn ($t) => ["/templates/{$t->slug}" => $t->updated_at]);

        $invitations = Invitation::where('status', 'published')
            ->get()
            ->mapWithKeys(fn ($i) => ["/i/{$i->subdomain}" => $i->updated_at]);

        $allPages = $staticPages->merge($templates)->merge($invitations);

        $xml = view('sitemap', compact('allPages', 'baseUrl'))->render();

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
