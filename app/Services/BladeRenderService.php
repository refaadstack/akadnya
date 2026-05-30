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

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
     * Build HTML asset tags (link and script).
     * Uses global CSS/JS with template configuration injected via CSS variables.
     */
    public function buildAssetTags(Template $template): string
    {
        $config = $this->getTemplateConfig($template);
        $tags = [];

        // Font Awesome for icons
        $tags[] = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">';

        // Google Fonts for typography
        $tags[] = '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@400;500;600&family=Dancing+Script:wght@400;600;700&display=swap">';

        // Global CSS (always loaded)
        $tags[] = '<link rel="stylesheet" href="'.asset('css/template-base.css').'">';
        $tags[] = '<link rel="stylesheet" href="'.asset('css/template-components.css').'">';

        // Inject CSS variables from template.json
        if (! empty($config['styling'])) {
            $tags[] = $this->buildCssVariables($config['styling']);
        }

        // Inject template configuration via meta tag (base64 encoded to avoid escaping issues)
        if (! empty($config)) {
            $configJson = json_encode($config);
            $configBase64 = base64_encode($configJson);
            $tags[] = "<meta name=\"template-config\" content=\"{$configBase64}\" data-encoding=\"base64\">";
        }

        // Global JS (loaded after config) with cache buster
        $tags[] = '<script src="'.asset('js/template-base.js').'"></script>';

        return implode("\n    ", $tags);
    }

    /**
     * Build CSS variables from template styling configuration
     */
    protected function buildCssVariables(array $styling): string
    {
        $variables = [];

        // Colors
        if (! empty($styling['colors'])) {
            foreach ($styling['colors'] as $key => $value) {
                $variables[] = "--color-{$key}: {$value}";
            }
        }

        // Fonts
        if (! empty($styling['fonts'])) {
            foreach ($styling['fonts'] as $key => $value) {
                $variables[] = "--font-{$key}: {$value}";
            }
        }

        // Spacing
        if (! empty($styling['spacing'])) {
            foreach ($styling['spacing'] as $key => $value) {
                $variables[] = "--spacing-{$key}: {$value}";
            }
        }

        // Border radius
        if (! empty($styling['borderRadius'])) {
            foreach ($styling['borderRadius'] as $key => $value) {
                $variables[] = "--radius-{$key}: {$value}";
            }
        }

        // Shadows
        if (! empty($styling['shadows'])) {
            foreach ($styling['shadows'] as $key => $value) {
                $variables[] = "--shadow-{$key}: {$value}";
            }
        }

        // Custom properties
        if (! empty($styling['custom'])) {
            foreach ($styling['custom'] as $key => $value) {
                $variables[] = "--{$key}: {$value}";
            }
        }

        if (empty($variables)) {
            return '';
        }

        $cssVars = implode(";\n        ", $variables);

        return <<<HTML
<style>
    :root {
        {$cssVars};
    }
</style>
HTML;
    }

    /**
     * Build complete stylesheet from template styling configuration
     * This generates actual CSS classes that work without Tailwind
     */
    protected function buildTemplateStylesheet(array $styling, string $slug): string
    {
        $colors = $styling['colors'] ?? [];
        $fonts = $styling['fonts'] ?? [];
        
        $primary = $colors['primary'] ?? '#dc2626';
        $secondary = $colors['secondary'] ?? '#fbbf24';
        $accent = $colors['accent'] ?? '#991b1b';
        $background = $colors['background'] ?? '#FFFAF0';
        $text = $colors['text'] ?? '#1f2937';
        
        $headingFont = $fonts['heading'] ?? "'Playfair Display', serif";
        $bodyFont = $fonts['body'] ?? "'Inter', sans-serif";
        
        $css = <<<CSS
<style>
/* Template-specific styles for {$slug} */
.template-{$slug} {
    font-family: {$bodyFont};
    color: {$text};
}

/* Layout */
.fixed { position: fixed; }
.inset-0 { top: 0; left: 0; right: 0; bottom: 0; }
.z-50 { z-index: 50; }
.z-40 { z-index: 40; }
.flex { display: flex; }
.inline-flex { display: inline-flex; }
.items-center { align-items: center; }
.justify-center { justify-content: center; }
.gap-2 { gap: 0.5rem; }

/* Background gradients */
.bg-gradient-to-br { background: linear-gradient(to bottom right, {$primary}, {$accent}); }

/* Text */
.text-center { text-align: center; }
.text-white { color: white; }
.text-sm { font-size: 0.875rem; }
.text-lg { font-size: 1.125rem; }
.text-xl { font-size: 1.25rem; }
.text-3xl { font-size: 1.875rem; }
.text-4xl { font-size: 2.25rem; }
.text-5xl { font-size: 3rem; }
.font-bold { font-weight: 700; }
.font-semibold { font-weight: 600; }
.uppercase { text-transform: uppercase; }
.tracking-widest { letter-spacing: 0.1em; }
.opacity-90 { opacity: 0.9; }
.opacity-70 { opacity: 0.7; }

/* Spacing */
.px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
.px-8 { padding-left: 2rem; padding-right: 2rem; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-6 { margin-bottom: 1.5rem; }
.mb-8 { margin-bottom: 2rem; }
.mt-6 { margin-top: 1.5rem; }

/* Colors from template.json */
.text-primary { color: {$secondary}; }
.bg-primary { background: {$secondary}; }
.text-accent { color: {$accent}; }

/* Buttons */
.rounded-full { border-radius: 9999px; }
.shadow-lg { box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
.transition-all { transition: all 0.3s; }
button, .btn {
    cursor: pointer;
    border: none;
    font-weight: 600;
}
button:active, .btn:active {
    transform: scale(0.95);
}

/* SVG */
.mx-auto { margin-left: auto; margin-right: auto; }

/* Music button */
.bottom-6 { bottom: 1.5rem; }
.right-6 { right: 1.5rem; }
.w-14 { width: 3.5rem; }
.h-14 { height: 3.5rem; }

/* Responsive */
@media (min-width: 768px) {
    .md\\:text-4xl { font-size: 2.25rem; }
    .md\\:text-5xl { font-size: 3rem; }
}
</style>
CSS;

        return $css;
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
