<?php

namespace App\Services;

use App\Exceptions\Template\TemplateManifestException;
use App\Exceptions\Template\TemplateSecurityException;
use App\Exceptions\Template\TemplateValidationException;
use App\Models\Template;
use App\Models\TemplateOrnament;
use App\Models\TemplateSection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class TemplateService
{
    public function __construct(
        protected TemplateZipValidator $validator
    ) {}

    /**
     * Process uploaded ZIP file: validate, extract, and sync to database.
     *
     * @return array{success: bool, message: string, template?: Template}
     */
    public function processUpload(string $zipPath): array
    {
        $tempDir = null;

        try {
            // Validate ZIP
            $validation = $this->validator->validate($zipPath);

            if (! $validation['valid']) {
                throw new TemplateValidationException(
                    'Template validation failed: '.implode(', ', $validation['errors'])
                );
            }

            $manifest = $validation['manifest'];
            $slug = $manifest['slug'];

            // Create temp directory for extraction
            $tempDir = sys_get_temp_dir().'/myakad_template_'.uniqid();
            File::makeDirectory($tempDir, 0755, true);

            // Extract ZIP to temp directory
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                throw new TemplateValidationException('Failed to open ZIP file for extraction.');
            }

            $zip->extractTo($tempDir);
            $zip->close();

            // Move atomically to storage/app/public/templates/{slug}/
            $finalPath = storage_path('app/public/templates/'.$slug);

            // Remove existing template directory if it exists
            if (File::exists($finalPath)) {
                File::deleteDirectory($finalPath);
            }

            // Ensure parent directory exists
            File::ensureDirectoryExists(storage_path('app/public/templates'));

            // Copy from temp to final location (Windows-compatible)
            File::copyDirectory($tempDir, $finalPath);

            // Delete temp directory after successful copy
            File::deleteDirectory($tempDir);
            $tempDir = null; // Mark as cleaned up

            // Sync to database
            $template = $this->syncTemplateFromDirectory($finalPath, $manifest);

            return [
                'success' => true,
                'message' => "Template '{$manifest['name']}' uploaded successfully.",
                'template' => $template,
            ];
        } catch (TemplateValidationException|TemplateSecurityException|TemplateManifestException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Template upload error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return [
                'success' => false,
                'message' => 'An unexpected error occurred during upload: '.$e->getMessage(),
            ];
        } finally {
            // Cleanup temp directory if it still exists
            if ($tempDir && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    /**
     * Sync a single template from directory to database.
     */
    protected function syncTemplateFromDirectory(string $folderPath, ?array $templateData = null): Template
    {
        if (! $templateData) {
            $templateData = $this->parseTemplateJson($folderPath);

            if (! $templateData) {
                throw new TemplateManifestException('Invalid or missing template.json');
            }
        }

        $slug = $templateData['slug'];

        // Upsert template
        $template = Template::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $templateData['name'],
                'thumbnail_url' => $templateData['thumbnail'] ?? null,
                'version' => $templateData['version'] ?? '1.0.0',
                'is_free' => $templateData['is_free'] ?? false,
                'price' => $templateData['price'] ?? 0,
                'is_active' => true,
                'synced_at' => now(),
            ]
        );

        // Sync sections
        $this->syncSections($template, $templateData['sections'] ?? []);

        // Sync ornaments
        $this->syncOrnaments($template, $templateData['ornaments'] ?? []);

        return $template;
    }

    /**
     * Scan storage/app/public/templates/ and sync to database
     */
    public function syncTemplates(): array
    {
        $synced = 0;
        $errors = [];
        $templatesPath = storage_path('app/public/templates');

        if (! File::exists($templatesPath)) {
            File::makeDirectory($templatesPath, 0755, true);

            return ['synced' => 0, 'errors' => ['Templates directory created. Please add template folders.']];
        }

        // Folders to exclude from sync (not templates)
        $excludedFolders = ['thumbnails', '.git', '.gitkeep', 'assets', 'shared'];

        $folders = File::directories($templatesPath);

        foreach ($folders as $folderPath) {
            $slug = basename($folderPath);

            // Skip excluded folders
            if (in_array($slug, $excludedFolders)) {
                continue;
            }

            try {
                $templateData = $this->parseTemplateJson($folderPath);

                if (! $templateData) {
                    $errors[] = "Invalid or missing template.json in {$slug}";

                    continue;
                }

                $validation = $this->validateTemplateFiles($folderPath, $templateData);

                if (! $validation['valid']) {
                    $errors[] = "{$slug}: ".implode(', ', $validation['errors']);

                    continue;
                }

                $this->syncTemplateFromDirectory($folderPath, $templateData);

                $synced++;
            } catch (\Exception $e) {
                $errors[] = "{$slug}: ".$e->getMessage();
                Log::error("Template sync error for {$slug}", ['error' => $e->getMessage()]);
            }
        }

        return ['synced' => $synced, 'errors' => $errors];
    }

    /**
     * Parse template.json and validate structure
     */
    public function parseTemplateJson(string $folderPath): ?array
    {
        $jsonPath = $folderPath.'/template.json';

        if (! File::exists($jsonPath)) {
            return null;
        }

        try {
            $content = File::get($jsonPath);
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            // Validate required fields
            if (empty($data['name']) || empty($data['slug'])) {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate template files exist
     */
    public function validateTemplateFiles(string $folderPath, array $templateData): array
    {
        $errors = [];

        // Check sections directory
        $sectionsPath = $folderPath.'/sections';
        if (! File::exists($sectionsPath)) {
            $errors[] = 'sections directory not found';
        } else {
            // Check each section file
            foreach ($templateData['sections'] ?? [] as $section) {
                $sectionFile = is_array($section) ? $section['file'] : $section.'.html';
                if (! File::exists($sectionsPath.'/'.$sectionFile)) {
                    $errors[] = "Section file not found: {$sectionFile}";
                }
            }
        }

        // Check ornaments directory
        $ornamentsPath = $folderPath.'/ornaments';
        if (File::exists($ornamentsPath)) {
            foreach ($templateData['ornaments'] ?? [] as $ornament) {
                $ornamentFile = $ornament['file'] ?? '';
                if (! File::exists($ornamentsPath.'/'.$ornamentFile)) {
                    $errors[] = "Ornament file not found: {$ornamentFile}";
                }
            }
        }

        // Check assets directory
        $assetsPath = $folderPath.'/assets';
        if (File::exists($assetsPath)) {
            $this->validateAssetDirectory($assetsPath, $errors);
        }

        $this->validateManifestAssets($folderPath, $templateData, $errors);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<int, string>  $errors
     */
    protected function validateAssetDirectory(string $assetsPath, array &$errors): void
    {
        $allowedExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'woff', 'woff2', 'ttf', 'otf'];

        foreach (File::allFiles($assetsPath) as $file) {
            $extension = strtolower($file->getExtension());

            if (! in_array($extension, $allowedExtensions, true)) {
                $errors[] = "Unsupported asset file type: {$file->getRelativePathname()}";
            }
        }
    }

    /**
     * @param  array<string, mixed>  $templateData
     * @param  array<int, string>  $errors
     */
    protected function validateManifestAssets(string $folderPath, array $templateData, array &$errors): void
    {
        if (! isset($templateData['assets'])) {
            return;
        }

        if (! is_array($templateData['assets'])) {
            $errors[] = 'assets must be an array';

            return;
        }

        foreach (['css' => ['css'], 'js' => ['js']] as $type => $allowedExtensions) {
            $assets = $templateData['assets'][$type] ?? [];

            if (is_string($assets)) {
                $assets = [$assets];
            }

            if (! is_array($assets)) {
                $errors[] = "assets.{$type} must be a string or array";

                continue;
            }

            foreach ($assets as $asset) {
                if (! is_string($asset)) {
                    $errors[] = "assets.{$type} may only contain file paths";

                    continue;
                }

                $path = $this->sanitizeAssetPath($asset, $allowedExtensions);

                if (! $path) {
                    $errors[] = "Invalid asset path: {$asset}";

                    continue;
                }

                if (! File::exists($folderPath.'/'.$path)) {
                    $errors[] = "Asset file not found: {$path}";
                }
            }
        }
    }

    /**
     * @param  array<int, string>  $allowedExtensions
     */
    protected function sanitizeAssetPath(string $path, array $allowedExtensions): ?string
    {
        $normalizedPath = str_replace('\\', '/', trim($path));
        $normalizedPath = ltrim($normalizedPath, '/');

        if (! str_starts_with($normalizedPath, 'assets/')) {
            $normalizedPath = 'assets/'.$normalizedPath;
        }

        if (
            $normalizedPath === 'assets/'
            || str_contains($normalizedPath, '..')
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
     * Sync sections for a template
     */
    protected function syncSections(Template $template, array $sections): void
    {
        // Delete existing sections
        $template->sections()->delete();

        foreach ($sections as $index => $section) {
            if (is_string($section)) {
                // Simple format: ["hero", "story", "gallery"]
                TemplateSection::create([
                    'template_id' => $template->id,
                    'file' => $section.'.html',
                    'label' => ucfirst($section),
                    'sort_order' => $index + 1,
                    'is_required' => in_array($section, ['hero', 'rsvp']),
                ]);
            } elseif (is_array($section)) {
                // Detailed format with file and label
                TemplateSection::create([
                    'template_id' => $template->id,
                    'file' => $section['file'],
                    'label' => $section['label'] ?? ucfirst(str_replace('.html', '', $section['file'])),
                    'sort_order' => $section['sort_order'] ?? $index + 1,
                    'is_required' => $section['is_required'] ?? false,
                ]);
            }
        }
    }

    /**
     * Sync ornaments for a template
     */
    protected function syncOrnaments(Template $template, array $ornaments): void
    {
        // Delete existing ornaments
        $template->ornaments()->delete();

        foreach ($ornaments as $ornament) {
            TemplateOrnament::create([
                'template_id' => $template->id,
                'file' => $ornament['file'],
                'label' => $ornament['label'] ?? ucfirst(str_replace('.html', '', $ornament['file'])),
                'position' => $ornament['position'] ?? 'top',
                'default_active' => $ornament['default_active'] ?? true,
            ]);
        }
    }
}
