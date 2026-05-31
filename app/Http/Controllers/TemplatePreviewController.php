<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Services\BladeRenderService;
use App\Services\DataContractBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API controller for template preview rendering.
 *
 * This endpoint is used by Preview.vue to render templates
 * with user-provided data before purchase.
 */
class TemplatePreviewController extends Controller
{
    public function __construct(
        protected BladeRenderService $bladeRenderer,
        protected DataContractBuilder $dataBuilder
    ) {}

    /**
     * Render template preview with custom data.
     *
     * Public endpoint - no authentication required.
     */
    public function render(Request $request, string $slug): JsonResponse
    {
        // Find active template
        $template = Template::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Get data from request, merge with template-owned defaults as fallback
        $defaultData = $this->dataBuilder->buildTemplateDefaults($template);
        $userData = $request->all();

        // Merge user data with template defaults (user data takes precedence)
        $data = array_replace($defaultData, array_filter($userData, fn ($value) => $value !== null && $value !== ''));

        // Render preview HTML
        $html = $this->bladeRenderer->renderPreview($template, $data);

        return response()->json([
            'html' => $html,
        ]);
    }
}
