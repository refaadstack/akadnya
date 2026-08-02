<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Template;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Renders templates using Blade engine.
 */
class BladeRenderService
{
    /**
     * Render a complete invitation page.
     */
    public function renderInvitation(Invitation $invitation, array $data): string
    {
        $template = $invitation->template;

        if ($this->isSingleFileTemplate($template)) {
            return $this->renderFullPage($template, $data);
        }

        // Get visible sections ordered by sort_order
        $sections = $invitation->sections()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();

        $html = '';

        // Render each visible section
        foreach ($sections as $section) {
            $sectionHtml = $this->renderSection($template, $section->templateSection->file, $data);
            $html .= $sectionHtml;
        }

        // Render active ornaments
        $ornaments = $invitation->ornaments()
            ->where('is_active', true)
            ->get();

        foreach ($ornaments as $ornament) {
            $ornamentHtml = $this->renderOrnament($template, $ornament->templateOrnament->file, $data);
            // Ornaments are typically positioned absolutely, so just append
            $html .= $ornamentHtml;
        }

        // Wrap with CSS isolation
        $html = $this->wrapWithCssIsolation($html, $template->slug);

        // Add asset tags
        $assetTags = $this->buildAssetTags($template);
        $csrfToken = $data['csrf_token'] ?? '';

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{$csrfToken}">
    <title>{$data['bride_name']} & {$data['groom_name']}</title>
    {$assetTags}
</head>
<body>
    {$html}
</body>
</html>
HTML;
    }

    /**
     * Render a template preview for admin panel.
     */
    public function renderPreview(Template $template, array $dummyData): string
    {
        if ($this->isSingleFileTemplate($template)) {
            $html = $this->renderFullPage($template, $dummyData);

            // Inject preview banner before closing body tag
            $banner = <<<'HTML'
<div style="position: fixed; top: 0; left: 0; right: 0; background: #f59e0b; color: white; padding: 0.75rem; text-align: center; z-index: 9999; font-family: sans-serif; font-size: 14px; font-weight: 600;">
    Preview Mode
</div>
HTML;

            return str_ireplace('</body>', $banner."\n</body>", $html);
        }

        // Get all sections ordered by sort_order
        $sections = $template->sections()->orderBy('sort_order')->get();

        $html = '';

        // Render each section
        foreach ($sections as $section) {
            $sectionHtml = $this->renderSection($template, $section->file, $dummyData);
            $html .= $sectionHtml;
        }

        // Wrap with CSS isolation
        $html = $this->wrapWithCssIsolation($html, $template->slug);

        // Add preview banner
        $banner = <<<HTML
<div style="position: fixed; top: 0; left: 0; right: 0; background: #f59e0b; color: white; padding: 0.75rem; text-align: center; z-index: 9999; font-family: sans-serif; font-size: 14px; font-weight: 600;">
    Preview Mode - Template: {$template->name}
</div>
<div style="height: 48px;"></div>
HTML;

        // Add asset tags
        $assetTags = $this->buildAssetTags($template);

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {$template->name}</title>
    {$assetTags}
</head>
<body>
    {$banner}
    {$html}
</body>
</html>
HTML;
    }

    /**
     * Render a single section.
     */
    public function renderSection(Template $template, string $sectionFile, array $data): string
    {
        $path = $template->getFolderPath().'/sections/'.$sectionFile;

        if (! File::exists($path)) {
            Log::warning("Section file not found: {$path}");

            return '';
        }

        try {
            $content = File::get($path);

            return Blade::render($content, $data);
        } catch (\Exception $e) {
            Log::error("Failed to render section: {$sectionFile}", [
                'error' => $e->getMessage(),
                'template' => $template->slug,
            ]);

            return '';
        }
    }

    /**
     * Render a single ornament.
     */
    public function renderOrnament(Template $template, string $ornamentFile, array $data): string
    {
        $path = $template->getFolderPath().'/ornaments/'.$ornamentFile;

        if (! File::exists($path)) {
            Log::warning("Ornament file not found: {$path}");

            return '';
        }

        try {
            $content = File::get($path);

            return Blade::render($content, $data);
        } catch (\Exception $e) {
            Log::error("Failed to render ornament: {$ornamentFile}", [
                'error' => $e->getMessage(),
                'template' => $template->slug,
            ]);

            return '';
        }
    }

    /**
     * Wrap HTML content with CSS isolation div.
     */
    public function wrapWithCssIsolation(string $html, string $slug): string
    {
        return <<<HTML
<div class="template-{$slug}">
{$html}
</div>
HTML;
    }

    /**
     * Determine if the template is a single-file template (full.html with inline styles).
     */
    public function isSingleFileTemplate(Template $template): bool
    {
        return (bool) ($this->getTemplateConfig($template)['single_file'] ?? false);
    }

    /**
     * Render a single-file template as a complete standalone HTML page.
     */
    public function renderFullPage(Template $template, array $data): string
    {
        $path = $template->getFolderPath().'/sections/full.html';

        if (! File::exists($path)) {
            Log::warning("Single-file template section not found: {$path}");

            return '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Template tidak ditemukan</title></head><body><p style="font-family:sans-serif;padding:2rem;">File template (full.html) tidak ditemukan.</p></body></html>';
        }

        return Blade::render(File::get($path), $data);
    }

    /**
     * Build HTML asset tags (link and script).
     * Loads only assets owned by the template package.
     */
    public function buildAssetTags(Template $template): string
    {
        $config = $this->getTemplateConfig($template);
        $tags = [];

        $tags = array_merge($tags, $this->buildTemplateCssTags($template, $config));

        // Inject template configuration via meta tag (base64 encoded to avoid escaping issues)
        if (! empty($config)) {
            $configJson = json_encode($config);
            $configBase64 = base64_encode($configJson);
            $tags[] = "<meta name=\"template-config\" content=\"{$configBase64}\" data-encoding=\"base64\">";
        }

        $tags = array_merge($tags, $this->buildTemplateScriptTags($template, $config));

        return implode("\n    ", $tags);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, string>
     */
    protected function buildTemplateCssTags(Template $template, array $config): array
    {
        return array_map(
            fn (string $path): string => '<link rel="stylesheet" href="'.e(route('templates.asset', ['slug' => $template->slug, 'file' => $path])).'">',
            $this->discoverTemplateAssets($template, $config, 'css', ['style.css'], ['css'])
        );
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, string>
     */
    protected function buildTemplateScriptTags(Template $template, array $config): array
    {
        return array_map(
            fn (string $path): string => '<script src="'.e(route('templates.asset', ['slug' => $template->slug, 'file' => $path])).'"></script>',
            $this->discoverTemplateAssets($template, $config, 'js', ['script.js'], ['js'])
        );
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<int, string>  $defaults
     * @param  array<int, string>  $allowedExtensions
     * @return array<int, string>
     */
    protected function discoverTemplateAssets(Template $template, array $config, string $type, array $defaults, array $allowedExtensions): array
    {
        $configuredAssets = $config['assets'][$type] ?? [];

        if (is_string($configuredAssets)) {
            $configuredAssets = [$configuredAssets];
        }

        if (! is_array($configuredAssets)) {
            $configuredAssets = [];
        }

        $assets = array_merge($defaults, $configuredAssets);
        $paths = [];

        foreach ($assets as $asset) {
            if (! is_string($asset)) {
                continue;
            }

            $path = $this->sanitizeTemplateAssetPath($asset, $allowedExtensions);

            if (! $path) {
                continue;
            }

            if (! File::exists($template->getFolderPath().'/assets/'.$path)) {
                continue;
            }

            $paths[] = $path;
        }

        return array_values(array_unique($paths));
    }

    /**
     * @param  array<int, string>  $allowedExtensions
     */
    protected function sanitizeTemplateAssetPath(string $path, array $allowedExtensions): ?string
    {
        $normalizedPath = str_replace('\\', '/', trim($path));
        $normalizedPath = ltrim($normalizedPath, '/');

        if (str_starts_with($normalizedPath, 'assets/')) {
            $normalizedPath = substr($normalizedPath, strlen('assets/'));
        }

        if (
            $normalizedPath === ''
            || str_contains($normalizedPath, '..')
            || str_starts_with($normalizedPath, '/')
            || preg_match('/^[a-zA-Z]:/', $normalizedPath) === 1
        ) {
            return null;
        }

        $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));

        if (! in_array($extension, $allowedExtensions, true)) {
            return null;
        }

        return $normalizedPath;
    }

    /**
     * Get template configuration from template.json
     */
    protected function getTemplateConfig(Template $template): array
    {
        $jsonPath = $template->getFolderPath().'/template.json';

        if (! File::exists($jsonPath)) {
            return [];
        }

        try {
            $content = File::get($jsonPath);

            return json_decode($content, true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
