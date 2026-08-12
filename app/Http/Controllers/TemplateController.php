<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Services\BladeRenderService;
use App\Services\DataContractBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
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
        $user = request()->user()?->load('grants');

        $templates = Template::active()
            ->orderBy('is_free', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn ($template) => [
                'id' => $template->id,
                'slug' => $template->slug,
                'name' => $template->name,
                'thumbnail_url' => $template->thumbnail_url, // Uses accessor
                'price' => (float) $template->price,
                'original_price' => $template->original_price !== null ? (float) $template->original_price : null,
                'discount_percent' => $template->discount_percent,
                'is_free' => $template->is_free,
                'is_granted' => $user?->hasTemplateAccess($template) ?? false,
            ]);

        return Inertia::render('Templates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Display template detail page with screenshots and inline test form.
     */
    public function show(string $slug): Response
    {
        $template = Template::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $description = '';
        $jsonPath = $template->getFolderPath().'/template.json';

        if (is_file($jsonPath)) {
            $config = json_decode((string) file_get_contents($jsonPath), true);
            $description = (string) ($config['description'] ?? '');
        }

        $screenshots = $this->buildScreenshotUrls($template->slug);

        $user = request()->user()?->load('grants');

        return Inertia::render('Templates/Show', [
            'template' => [
                'id' => $template->id,
                'slug' => $template->slug,
                'name' => $template->name,
                'thumbnail_url' => $template->thumbnail_url,
                'price' => (float) $template->price,
                'original_price' => $template->original_price !== null ? (float) $template->original_price : null,
                'discount_percent' => $template->discount_percent,
                'is_free' => $template->is_free,
                'is_granted' => $user?->hasTemplateAccess($template) ?? false,
                'description' => $description,
                'screenshots' => $screenshots,
            ],
            'preview_defaults' => $this->buildPreviewDefaults($template),
        ]);
    }

    /**
     * Build screenshot asset URLs for a template, mobile cover first.
     *
     * @return array<int, string>
     */
    protected function buildScreenshotUrls(string $slug): array
    {
        $dir = storage_path("app/public/templates/shots/{$slug}");

        if (! is_dir($dir)) {
            return [];
        }

        $preferred = ['mobile-cover.png', 'mobile-full.png', 'desktop.png'];
        $files = collect(File::files($dir))
            ->filter(fn ($file) => in_array($file->getFilename(), $preferred, true))
            ->sortBy(fn ($file) => array_search($file->getFilename(), $preferred, true));

        return $files
            ->map(fn ($file) => asset("storage/templates/shots/{$slug}/{$file->getFilename()}"))
            ->values()
            ->all();
    }

    /**
     * Build preview form defaults from the template's own sample data.
     *
     * @return array<string, mixed>
     */
    protected function buildPreviewDefaults(Template $template): array
    {
        $allowed = [
            'greeting', 'groom_name', 'bride_name',
            'groom_father', 'groom_mother', 'bride_father', 'bride_mother',
            'akad_datetime', 'akad_datetime_formatted', 'akad_time', 'akad_venue', 'akad_maps_url',
            'reception_datetime', 'reception_datetime_formatted', 'reception_time', 'reception_venue', 'reception_maps_url',
            'mappacci_datetime_formatted', 'mappacci_time', 'mappacci_venue',
            'love_story', 'special_message', 'guest_name',
        ];

        $defaults = $this->dataBuilder->buildTemplateDefaults($template);

        return array_intersect_key($defaults, array_flip($allowed));
    }

    /**
     * Redirect legacy preview URLs to the standalone template renderer.
     */
    public function preview(string $slug): RedirectResponse
    {
        Template::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return redirect()->route('templates.render', [
            'slug' => $slug,
            ...request()->query(),
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

        $data = $this->dataBuilder->buildTemplateDefaults($template);

        // Check if custom data is provided via query parameters
        $customData = request()->query('data');

        if ($customData) {
            // Decode base64-encoded JSON data from query string
            try {
                $decodedData = json_decode(base64_decode($customData), true);
                if (is_array($decodedData)) {
                    $data = array_replace($data, array_filter($decodedData, fn ($value) => $value !== null && $value !== ''));
                }
            } catch (\Exception $e) {
                // Fall back to template defaults if decoding fails
            }
        }

        $data['video_youtube_id'] = $this->dataBuilder->extractYoutubeId($data['video_url'] ?? null);

        $html = $this->bladeRenderer->renderPreview($template, $data);

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('Cross-Origin-Opener-Policy', 'same-origin-allow-popups')
            ->header('Cross-Origin-Embedder-Policy', 'unsafe-none');
    }
}
