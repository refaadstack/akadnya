<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixTemplateSyntax extends Command
{
    protected $signature = 'templates:fix-syntax';
    protected $description = 'Fix Mustache syntax to Blade syntax in all template files';

    public function handle()
    {
        $this->info('Fixing template syntax from Mustache to Blade...');

        $templatesPath = public_path('templates');
        $fixed = 0;
        $errors = [];

        if (!File::exists($templatesPath)) {
            $this->error('Templates directory not found');
            return 1;
        }

        $folders = File::directories($templatesPath);

        foreach ($folders as $folderPath) {
            $slug = basename($folderPath);
            $this->info("Processing template: {$slug}");

            // Fix sections
            $sectionsPath = $folderPath . '/sections';
            if (File::exists($sectionsPath)) {
                $sectionFiles = File::files($sectionsPath);
                foreach ($sectionFiles as $file) {
                    try {
                        $content = File::get($file);
                        $originalContent = $content;

                        // Fix Mustache variables to Blade with null coalescing
                        // {{variable}} -> {{ $variable ?? '' }}
                        $content = preg_replace('/\{\{([a-z_]+)\}\}/', '{{ $\1 ?? \'\' }}', $content);

                        // Fix Mustache conditionals to Blade
                        // {{#variable}} -> @if($variable)
                        $content = preg_replace('/\{\{#([a-z_]+)\}\}/', '@if($\1)', $content);
                        
                        // {{/variable}} -> @endif
                        $content = preg_replace('/\{\{\/[a-z_]+\}\}/', '@endif', $content);

                        // Fix Mustache loops to Blade
                        // {{#gallery_urls}} -> @foreach($gallery ?? [] as $item)
                        if (strpos($content, '@if($gallery_urls)') !== false) {
                            $content = str_replace('@if($gallery_urls)', '@foreach($gallery ?? [] as $item)', $content);
                            $content = str_replace('{{ $url ?? \'\' }}', '{{ $item[\'url\'] ?? \'\' }}', $content);
                            $content = str_replace('{{ $caption ?? \'\' }}', '{{ $item[\'caption\'] ?? \'\' }}', $content);
                            $content = str_replace('@if($caption)', '@if($item[\'caption\'] ?? false)', $content);
                        }

                        // Fix event_date in JavaScript
                        $content = str_replace("new Date('{{ $event_date ?? '' }}')", "new Date('{{ $event_date ?? '' }}')", $content);

                        if ($content !== $originalContent) {
                            File::put($file, $content);
                            $fixed++;
                            $this->line("  ✓ Fixed: " . basename($file));
                        }
                    } catch (\Exception $e) {
                        $errors[] = "{$slug}/sections/" . basename($file) . ": " . $e->getMessage();
                    }
                }
            }

            // Fix ornaments
            $ornamentsPath = $folderPath . '/ornaments';
            if (File::exists($ornamentsPath)) {
                $ornamentFiles = File::files($ornamentsPath);
                foreach ($ornamentFiles as $file) {
                    try {
                        $content = File::get($file);
                        $originalContent = $content;

                        // Same fixes as sections
                        $content = preg_replace('/\{\{([a-z_]+)\}\}/', '{{ $\1 ?? \'\' }}', $content);
                        $content = preg_replace('/\{\{#([a-z_]+)\}\}/', '@if($\1)', $content);
                        $content = preg_replace('/\{\{\/[a-z_]+\}\}/', '@endif', $content);

                        if ($content !== $originalContent) {
                            File::put($file, $content);
                            $fixed++;
                            $this->line("  ✓ Fixed: ornaments/" . basename($file));
                        }
                    } catch (\Exception $e) {
                        $errors[] = "{$slug}/ornaments/" . basename($file) . ": " . $e->getMessage();
                    }
                }
            }
        }

        $this->newLine();
        $this->info("✓ Fixed {$fixed} file(s)");

        if (count($errors) > 0) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($errors as $error) {
                $this->line("  • {$error}");
            }
            return 1;
        }

        $this->newLine();
        $this->info('All templates fixed successfully!');
        $this->info('Run: php artisan templates:sync');

        return 0;
    }
}
