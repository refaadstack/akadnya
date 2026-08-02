<?php

use App\Models\Template;
use App\Services\TemplateService;
use App\Services\TemplateZipValidator;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->validator = new TemplateZipValidator;
    $this->service = new TemplateService($this->validator);
    $this->tempDir = sys_get_temp_dir().'/myakad_test_'.uniqid();
    mkdir($this->tempDir, 0777, true);
});

afterEach(function () {
    // Cleanup temp directory
    if (is_dir($this->tempDir)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($this->tempDir);
    }

    // Cleanup public/templates/test-* directories
    $publicTemplates = storage_path('app/public/templates');
    if (is_dir($publicTemplates)) {
        $dirs = glob($publicTemplates.'/test-*');
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }
    }});

test('processUpload with valid ZIP creates template in public/templates', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'test-template-'.uniqid(),
        'name' => 'Test Template',
        'version' => '1.0.0',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->addFromString('assets/style.css', 'body { margin: 0; }');
    $zip->close();

    $result = $this->service->processUpload($zipPath);

    expect($result['success'])->toBeTrue();
    expect($result['template'])->toBeInstanceOf(Template::class);

    $slug = $result['template']->slug;
    expect(File::exists(storage_path("app/public/templates/{$slug}/template.json")))->toBeTrue();
    expect(File::exists(storage_path("app/public/templates/{$slug}/sections/cover.html")))->toBeTrue();
    expect(File::exists(storage_path("app/public/templates/{$slug}/assets/style.css")))->toBeTrue();

    // Verify database record
    $template = Template::where('slug', $slug)->first();
    expect($template)->not->toBeNull();
    expect($template->name)->toBe('Test Template');
});

test('processUpload with invalid ZIP does not create partial files', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'test-invalid-'.uniqid(),
        'name' => 'Invalid Template',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
    ]));
    // Missing assets/style.css
    $zip->close();

    $result = $this->service->processUpload($zipPath);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('validation failed');

    // Verify no files were created in public/templates
    $slug = 'test-invalid-'.substr($zipPath, -13);
    expect(File::exists(storage_path("app/public/templates/{$slug}")))->toBeFalse();
});

test('processUpload with valid HTML file creates single-file template', function () {
    $slug = 'test-html-'.uniqid();
    $htmlPath = $this->tempDir.'/'.$slug.'.html';
    File::put($htmlPath, '<!DOCTYPE html><html lang="id"><head><title>Undangan</title></head><body><h1>{{ $bride_name }}</h1></body></html>');

    $result = $this->service->processUpload($htmlPath);

    expect($result['success'])->toBeTrue();
    expect($result['template'])->toBeInstanceOf(Template::class);
    expect($result['template']->slug)->toBe($slug);

    expect(File::exists(storage_path("app/public/templates/{$slug}/sections/full.html")))->toBeTrue();
    expect(File::exists(storage_path("app/public/templates/{$slug}/template.json")))->toBeTrue();

    $config = json_decode(File::get(storage_path("app/public/templates/{$slug}/template.json")), true);
    expect($config['single_file'])->toBeTrue();
    expect($config['sections'][0]['file'])->toBe('full.html');
    expect($config['assets'])->toBeArray();
});

test('processUpload with invalid HTML file fails without creating files', function () {
    $slug = 'test-bad-'.uniqid();
    $htmlPath = $this->tempDir.'/'.$slug.'.html';
    File::put($htmlPath, 'this is not html');

    $result = $this->service->processUpload($htmlPath);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('validation failed');
    expect(File::exists(storage_path("app/public/templates/{$slug}")))->toBeFalse();
});

test('syncTemplates from empty directory returns zero synced', function () {
    $emptyPath = $this->tempDir.'/empty-templates';
    File::makeDirectory($emptyPath, 0755, true);

    $result = $this->service->syncTemplates($emptyPath);

    expect($result['synced'])->toBe(0);
});

test('syncTemplates with valid directory creates database record', function () {
    $slug = 'test-sync-'.uniqid();
    $templatePath = storage_path("app/public/templates/{$slug}");

    // Create template structure
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::makeDirectory($templatePath.'/assets', 0755, true);

    File::put($templatePath.'/template.json', json_encode([
        'slug' => $slug,
        'name' => 'Sync Test Template',
        'version' => '1.0.0',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
    ]));

    File::put($templatePath.'/sections/cover.html', '<h1>Cover</h1>');
    File::put($templatePath.'/assets/style.css', 'body {}');

    $result = $this->service->syncTemplates();

    expect($result['synced'])->toBeGreaterThanOrEqual(1);

    $template = Template::where('slug', $slug)->first();
    expect($template)->not->toBeNull();
    expect($template->name)->toBe('Sync Test Template');
    expect($template->synced_at)->not->toBeNull();
});

test('processUpload replaces existing template directory', function () {
    $slug = 'test-replace-'.uniqid();

    // Create existing template
    $existingPath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($existingPath, 0755, true);
    File::put($existingPath.'/old-file.txt', 'old content');

    // Upload new template with same slug
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => $slug,
        'name' => 'Replaced Template',
        'version' => '2.0.0',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
    ]));
    $zip->addFromString('sections/cover.html', '<h1>New Cover</h1>');
    $zip->addFromString('assets/style.css', 'body {}');
    $zip->close();

    $result = $this->service->processUpload($zipPath);

    expect($result['success'])->toBeTrue();

    // Old file should not exist
    expect(File::exists($existingPath.'/old-file.txt'))->toBeFalse();

    // New files should exist
    expect(File::exists($existingPath.'/sections/cover.html'))->toBeTrue();
    expect(File::get($existingPath.'/sections/cover.html'))->toContain('New Cover');
});
