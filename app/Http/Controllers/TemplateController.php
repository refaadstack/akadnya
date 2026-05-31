<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Services\BladeRenderService;
use App\Services\DataContractBuilder;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TemplateController extends Controller
{
    public function __construct(
        protected BladeRenderService $bladeRenderer,
        protected DataContractBuilder $dataBuilder
    ) {}

    /**
     * Display all active templates
     */
    public function index(): Response
    {
        $templates = Template::active()
            ->orderBy('is_free', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn ($template) => [
                'id' => $template->id,
                'slug' => $template->slug,
                'name' => $template->name,
                'thumbnail_url' => $template->thumbnail_url, // Uses accessor
                'price' => $template->price,
                'is_free' => $template->is_free,
            ]);

        return Inertia::render('Templates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Preview template with dummy data in Inertia page
     */
    public function preview(string $slug): Response
    {
        $template = Template::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Templates/Preview', [
            'template' => [
                'id' => $template->id,
                'slug' => $template->slug,
                'name' => $template->name,
                'price' => $template->price,
                'is_free' => $template->is_free,
            ],
        ]);
    }

    /**
     * Render template HTML for iframe
     */
    public function render(string $slug): SymfonyResponse
    {
        $template = Template::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Check if custom data is provided via query parameters
        $customData = request()->query('data');

        if ($customData) {
            // Decode base64-encoded JSON data from query string
            try {
                $decodedData = json_decode(base64_decode($customData), true);
                $data = array_merge($this->dataBuilder->buildDummy(), $decodedData);
            } catch (\Exception $e) {
                // Fall back to dummy data if decoding fails
                $data = $this->dataBuilder->buildDummy();
            }
        } else {
            $data = $this->dataBuilder->buildDummy();
        }

        $html = $this->bladeRenderer->renderPreview($template, $data);

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('Cross-Origin-Opener-Policy', 'same-origin-allow-popups')
            ->header('Cross-Origin-Embedder-Policy', 'unsafe-none');
    }
}
