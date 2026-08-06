<?php

use App\Models\Template;
use App\Services\BladeRenderService;
use App\Services\DataContractBuilder;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->service = new BladeRenderService;
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

    // Cleanup storage template test directories
    $publicTemplates = storage_path('app/public/templates');
    if (is_dir($publicTemplates)) {
        $dirs = glob($publicTemplates.'/test-*');
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }
    }
});

test('renderSection returns empty string when file not found', function () {
    $template = Template::factory()->create(['slug' => 'test-'.uniqid()]);

    $result = $this->service->renderSection($template, 'nonexistent.html', []);

    expect($result)->toBe('');
});

test('renderSection renders Blade template with data', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);

    // Create template structure
    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::put($templatePath.'/sections/cover.html', '<h1>{{ $bride_name }} & {{ $groom_name }}</h1>');

    $result = $this->service->renderSection($template, 'cover.html', [
        'bride_name' => 'Siti',
        'groom_name' => 'Budi',
    ]);

    expect($result)->toContain('Siti & Budi');
    expect($result)->toContain('<h1>');
});

test('renderSection handles Blade directives', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::put($templatePath.'/sections/test.html', '@if($show)<p>Visible</p>@endif');

    $result = $this->service->renderSection($template, 'test.html', ['show' => true]);
    expect($result)->toContain('Visible');

    $result = $this->service->renderSection($template, 'test.html', ['show' => false]);
    expect($result)->not->toContain('Visible');
});

test('wrapWithCssIsolation wraps content in template-specific div', function () {
    $html = '<p>Content</p>';
    $slug = 'elegant-rose';

    $result = $this->service->wrapWithCssIsolation($html, $slug);

    expect($result)->toContain('class="template-elegant-rose"');
    expect($result)->toContain('<p>Content</p>');
});

test('buildAssetTags generates link tag for existing CSS', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/assets/style.css', 'body { margin: 0; }');

    $result = $this->service->buildAssetTags($template);

    expect($result)->toContain('<link rel="stylesheet"');
    expect($result)->toContain("template-assets/{$slug}/style.css");
});

test('buildAssetTags generates script tag for existing JS', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/assets/script.js', 'console.log("test");');

    $result = $this->service->buildAssetTags($template);

    expect($result)->toContain('<script src=');
    expect($result)->toContain("template-assets/{$slug}/script.js");
});

test('buildAssetTags returns empty string when no assets exist', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);

    $result = $this->service->buildAssetTags($template);

    expect($result)->toBe('');
});

test('buildAssetTags generates both CSS and JS tags when both exist', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/assets/style.css', 'body {}');
    File::put($templatePath.'/assets/script.js', 'console.log("test");');

    $result = $this->service->buildAssetTags($template);

    expect($result)->toContain('<link rel="stylesheet"');
    expect($result)->toContain('<script src=');
    expect($result)->not->toContain('template-base.css');
    expect($result)->not->toContain('template-components.css');
    expect($result)->not->toContain('template-base.js');
});

test('renderOrnament returns empty string when file not found', function () {
    $template = Template::factory()->create(['slug' => 'test-'.uniqid()]);

    $result = $this->service->renderOrnament($template, 'nonexistent.html', []);

    expect($result)->toBe('');
});

test('renderOrnament renders Blade template with data', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/ornaments', 0755, true);
    File::put($templatePath.'/ornaments/flower.html', '<div class="flower">{{ $color }}</div>');

    $result = $this->service->renderOrnament($template, 'flower.html', ['color' => 'red']);

    expect($result)->toContain('red');
    expect($result)->toContain('flower');
});

test('renderSection emits no undefined variable warnings when payment data is missing', function () {
    $contractBuilder = new DataContractBuilder;
    $paymentKeys = [
        'qris_image_url', 'gopay_number', 'ovo_number', 'dana_number',
        'bank_name', 'account_number', 'account_name', 'gift_address',
    ];

    $files = [
        'red-cream' => ['full.html', 'class="rg-qris"'],
        '01kz1heyw9mwm46c7xgqvegrqh' => ['full.html', 'class="rg-qris"'],
        'melayu-jambi' => ['gift.html', 'jambi-gift-qris'],
        'sunda-merbak' => ['gift.html', 'sunda-gift-qris'],
        'chinese-imperial-luxe' => ['gift.html', 'ci-qris'],
    ];

    foreach ($files as $slug => [$file, $qrisClass]) {
        $template = (new Template)->forceFill(['slug' => $slug]);
        $data = $contractBuilder->buildTemplateDefaults($template);
        $data = array_diff_key($data, array_flip($paymentKeys));

        $warnings = [];
        set_error_handler(function (int $severity, string $message) use (&$warnings) {
            if (str_contains($message, 'Undefined variable')) {
                $warnings[] = $message;
            }

            return true;
        });

        try {
            $result = $this->service->renderSection($template, $file, $data);
        } finally {
            restore_error_handler();
        }

        expect($warnings)->toBeEmpty("{$slug}: rendered without undefined variable warnings");
        expect($result)->not->toBeEmpty();
        expect($result)->not->toContain($qrisClass);
    }
});

test('cover sections render guest name when provided', function () {
    $contractBuilder = new DataContractBuilder;
    $files = [
        'red-cream' => 'full.html',
        '01kz1heyw9mwm46c7xgqvegrqh' => 'full.html',
        'chinese-imperial-luxe' => 'opening.html',
        'klasik-elegan' => 'opening.html',
    ];

    foreach ($files as $slug => $file) {
        $template = (new Template)->forceFill(['slug' => $slug]);
        $data = $contractBuilder->buildTemplateDefaults($template);
        $data['guest_name'] = 'Bapak Ahmad';

        $warnings = [];
        set_error_handler(function (int $severity, string $message) use (&$warnings) {
            if (str_contains($message, 'Undefined variable')) {
                $warnings[] = $message;
            }

            return true;
        });

        try {
            $result = $this->service->renderSection($template, $file, $data);
        } finally {
            restore_error_handler();
        }

        expect($warnings)->toBeEmpty("{$slug}: rendered without undefined variable warnings");
        expect($result)->toContain('Kepada Yth. Bapak Ahmad');
    }
});

test('cover sections fall back to generic guest label without guest name', function () {
    $contractBuilder = new DataContractBuilder;
    $files = [
        'red-cream' => 'full.html',
        '01kz1heyw9mwm46c7xgqvegrqh' => 'full.html',
        'chinese-imperial-luxe' => 'opening.html',
        'klasik-elegan' => 'opening.html',
    ];

    foreach ($files as $slug => $file) {
        $template = (new Template)->forceFill(['slug' => $slug]);
        $data = $contractBuilder->buildTemplateDefaults($template);
        unset($data['guest_name']);

        $result = $this->service->renderSection($template, $file, $data);

        expect($result)->toContain('Kepada Yth. Tamu Undangan');
    }
});
