<?php

namespace App\Services;

use App\Exceptions\Template\TemplateManifestException;
use App\Exceptions\Template\TemplateSecurityException;
use App\Exceptions\Template\TemplateValidationException;
use ZipArchive;

/**
 * Validates template ZIP packages for security and structural integrity.
 */
class TemplateZipValidator
{
    /**
     * Validate a template ZIP file.
     *
     * @return array{valid: bool, errors: string[], manifest: array<string, mixed>|null}
     */
    public function validate(string $zipPath): array
    {
        $errors = [];
        $manifest = null;

        if (! file_exists($zipPath)) {
            return [
                'valid' => false,
                'errors' => ['❌ ZIP file does not exist at the specified path.'],
                'manifest' => null,
            ];
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            return [
                'valid' => false,
                'errors' => ['❌ Failed to open ZIP file. The file may be corrupted or not a valid ZIP archive.'],
                'manifest' => null,
            ];
        }

        try {
            // Check for path traversal
            if ($this->containsPathTraversal($zip)) {
                $errors[] = '🔒 ZIP contains unsafe paths (path traversal detected). This is a security risk.';
            }

            // Check for template.json
            $manifestContent = $zip->getFromName('template.json');
            if ($manifestContent === false) {
                $errors[] = '📄 template.json not found in ZIP root. This file is required and must be in the root of the ZIP.';
                $zip->close();

                return [
                    'valid' => false,
                    'errors' => $errors,
                    'manifest' => null,
                ];
            }

            // Parse template.json
            try {
                $manifest = json_decode($manifestContent, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $errors[] = '⚠️ template.json contains invalid JSON syntax: '.$e->getMessage();
                $zip->close();

                return [
                    'valid' => false,
                    'errors' => $errors,
                    'manifest' => null,
                ];
            }

            // Validate required fields in manifest
            $requiredFields = ['slug', 'name'];
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (! isset($manifest[$field]) || empty($manifest[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (! empty($missingFields)) {
                $errors[] = '📋 template.json is missing required fields: '.implode(', ', $missingFields).'. These fields are mandatory.';
            }

            // Validate slug format
            if (isset($manifest['slug']) && ! preg_match('/^[a-z0-9-]+$/', $manifest['slug'])) {
                $errors[] = '🔤 Invalid slug format. Slug must contain only lowercase letters, numbers, and hyphens (e.g., "romantic-wedding").';
            }

            // Check if sections array exists
            if (! isset($manifest['sections']) || ! is_array($manifest['sections']) || empty($manifest['sections'])) {
                $errors[] = '📁 template.json must contain a "sections" array with at least one section.';
            } else {
                // Validate sections exist
                $missingSections = [];
                foreach ($manifest['sections'] as $index => $section) {
                    $sectionFile = is_array($section) ? ($section['file'] ?? null) : $section;
                    if (! $sectionFile) {
                        $errors[] = "⚠️ Section at index {$index} is missing 'file' property.";

                        continue;
                    }

                    // Check if file path already includes sections/
                    $path = str_starts_with($sectionFile, 'sections/') ? $sectionFile : 'sections/'.$sectionFile;

                    if ($zip->locateName($path) === false) {
                        $missingSections[] = $path;
                    } elseif (! $this->isValidTextFile($zip, $path)) {
                        $errors[] = "📄 Section file '{$path}' is not a valid text/HTML file or contains binary data.";
                    }
                }

                if (! empty($missingSections)) {
                    $errors[] = '📁 Missing section files: '.implode(', ', $missingSections).'. All sections listed in template.json must exist.';
                }
            }

            // Validate ornaments exist (if specified)
            if (isset($manifest['ornaments']) && is_array($manifest['ornaments'])) {
                $missingOrnaments = [];
                foreach ($manifest['ornaments'] as $ornament) {
                    $ornamentFile = is_array($ornament) ? ($ornament['file'] ?? null) : $ornament;
                    if ($ornamentFile) {
                        $path = str_starts_with($ornamentFile, 'ornaments/') ? $ornamentFile : 'ornaments/'.$ornamentFile;
                        if ($zip->locateName($path) === false) {
                            $missingOrnaments[] = $path;
                        } elseif (! $this->isValidTextFile($zip, $path)) {
                            $errors[] = "📄 Ornament file '{$path}' is not a valid text/HTML file.";
                        }
                    }
                }

                if (! empty($missingOrnaments)) {
                    $errors[] = '🎨 Missing ornament files: '.implode(', ', $missingOrnaments);
                }
            }

            // Validate styling configuration in template.json
            if (isset($manifest['styling'])) {
                if (! is_array($manifest['styling'])) {
                    $errors[] = '🎨 "styling" must be an object/array in template.json.';
                } else {
                    // Validate styling structure (optional but recommended)
                    $validStylingKeys = ['colors', 'fonts', 'spacing', 'borderRadius', 'shadows', 'custom'];
                    foreach (array_keys($manifest['styling']) as $key) {
                        if (! in_array($key, $validStylingKeys)) {
                            $errors[] = "⚠️ Unknown styling key '{$key}'. Valid keys: ".implode(', ', $validStylingKeys);
                        }
                    }
                }
            }

            // Validate features configuration in template.json
            if (isset($manifest['features'])) {
                if (! is_array($manifest['features'])) {
                    $errors[] = '⚙️ "features" must be an object/array in template.json.';
                } else {
                    // Validate features structure (optional but recommended)
                    $validFeatureKeys = ['countdown', 'music', 'opening', 'gallery', 'animations'];
                    foreach (array_keys($manifest['features']) as $key) {
                        if (! in_array($key, $validFeatureKeys)) {
                            $errors[] = "⚠️ Unknown feature key '{$key}'. Valid keys: ".implode(', ', $validFeatureKeys);
                        }
                    }
                }
            }

            // NOTE: Custom CSS/JS files are NO LONGER REQUIRED
            // Templates now use global CSS/JS controlled by template.json configuration
            // If custom assets exist, they will be ignored in favor of global system

            // Check for common mistakes
            $this->checkCommonMistakes($zip, $errors);

            $zip->close();

            return [
                'valid' => empty($errors),
                'errors' => $errors,
                'manifest' => $manifest,
            ];
        } catch (\Exception $e) {
            $zip->close();

            return [
                'valid' => false,
                'errors' => ['❌ Validation error: '.$e->getMessage()],
                'manifest' => null,
            ];
        }
    }

    /**
     * Check for common mistakes in ZIP structure.
     */
    protected function checkCommonMistakes(ZipArchive $zip, array &$errors): void
    {
        // Check if files are nested in a parent folder
        $hasRootFolder = false;
        for ($i = 0; $i < min($zip->numFiles, 10); $i++) {
            $stat = $zip->statIndex($i);
            if ($stat && str_contains($stat['name'], '/')) {
                $parts = explode('/', $stat['name']);
                if (count($parts) > 1 && $parts[0] !== 'sections' && $parts[0] !== 'assets' && $parts[0] !== 'ornaments') {
                    $hasRootFolder = true;
                    break;
                }
            }
        }

        if ($hasRootFolder) {
            $errors[] = '📦 ZIP appears to have an extra parent folder. Files should be in the root of the ZIP, not nested in a folder.';
        }

        // Check for __MACOSX folder (common Mac issue)
        if ($zip->locateName('__MACOSX/') !== false) {
            $errors[] = '🍎 ZIP contains __MACOSX folder. Please remove this before uploading (Mac users: use "zip -r" command or clean-zip tool).';
        }
    }

    /**
     * Check if ZIP contains path traversal attempts.
     */
    public function containsPathTraversal(ZipArchive $zip): bool
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                continue;
            }

            $name = $stat['name'];

            // Check for path traversal patterns
            if (str_contains($name, '..') || str_starts_with($name, '/')) {
                return true;
            }

            // Check for absolute paths (Windows)
            if (preg_match('/^[a-zA-Z]:/', $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate that a file is a valid text file (not binary).
     */
    public function isValidTextFile(ZipArchive $zip, string $entryName): bool
    {
        $content = $zip->getFromName($entryName);

        if ($content === false) {
            return false;
        }

        // Check if content is valid UTF-8
        if (! mb_check_encoding($content, 'UTF-8')) {
            return false;
        }

        // Check for null bytes (common in binary files)
        if (str_contains($content, "\0")) {
            return false;
        }

        return true;
    }
}
