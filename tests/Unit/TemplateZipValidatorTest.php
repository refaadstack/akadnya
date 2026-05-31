<?php

use App\Services\TemplateZipValidator;

beforeEach(function () {
    $this->validator = new TemplateZipValidator;
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
});

test('validates ZIP without template.json returns error', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'][0])->toContain('template.json not found');
    expect($result['manifest'])->toBeNull();
});

test('validates ZIP with invalid JSON in template.json returns error', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', '{invalid json}');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'][0])->toContain('invalid JSON');
});

test('validates ZIP without required fields returns error', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode(['version' => '1.0.0']));
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'][0])->toContain('missing required fields');
    expect($result['errors'][0])->toContain('slug');
    expect($result['errors'][0])->toContain('name');
});

test('validates ZIP without sections array returns error', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'test-template',
        'name' => 'Test Template',
    ]));
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'][0])->toContain('must contain a "sections" array');
});

test('validates ZIP with missing section files returns all missing files', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'test-template',
        'name' => 'Test Template',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
            ['file' => 'gallery.html', 'label' => 'Gallery'],
            ['file' => 'rsvp.html', 'label' => 'RSVP'],
        ],
    ]));
    // Only add cover.html, missing gallery.html and rsvp.html
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'])->toHaveCount(1);
    expect($result['errors'][0])->toContain('Missing section files');
    expect($result['errors'][0])->toContain('sections/gallery.html');
    expect($result['errors'][0])->toContain('sections/rsvp.html');
});

test('detects path traversal with double dots', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode(['slug' => 'test', 'name' => 'Test']));
    $zip->addFromString('../../../etc/passwd', 'malicious');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'][0])->toContain('unsafe paths');
});

test('detects path traversal with leading slash', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode(['slug' => 'test', 'name' => 'Test']));
    $zip->addFromString('/etc/passwd', 'malicious');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'][0])->toContain('unsafe paths');
});

test('containsPathTraversal returns true for various traversal patterns', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('../../malicious.txt', 'bad');
    $zip->close();

    $zip->open($zipPath);
    $result = $this->validator->containsPathTraversal($zip);
    $zip->close();

    expect($result)->toBeTrue();
});

test('containsPathTraversal returns false for safe paths', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('sections/cover.html', '<h1>Safe</h1>');
    $zip->addFromString('assets/style.css', 'body {}');
    $zip->close();

    $zip->open($zipPath);
    $result = $this->validator->containsPathTraversal($zip);
    $zip->close();

    expect($result)->toBeFalse();
});

test('validates complete valid ZIP returns success', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'elegant-rose',
        'name' => 'Elegant Rose',
        'version' => '1.0.0',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
        'ornaments' => [
            ['file' => 'flower.html', 'label' => 'Flower'],
        ],
        'styling' => [
            'colors' => [
                'primary' => '#dc2626',
                'secondary' => '#fbbf24',
            ],
        ],
        'features' => [
            'countdown' => true,
            'music' => true,
        ],
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->addFromString('ornaments/flower.html', '<div>Flower</div>');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeTrue();
    expect($result['errors'])->toBeEmpty();
    expect($result['manifest'])->toBeArray();
    expect($result['manifest']['slug'])->toBe('elegant-rose');
});

test('validates ZIP with configured template assets returns success', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'custom-assets',
        'name' => 'Custom Assets',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
        'assets' => [
            'css' => ['assets/style.css'],
            'js' => ['script.js'],
        ],
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->addFromString('assets/style.css', 'body {}');
    $zip->addFromString('assets/script.js', 'console.log("template");');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeTrue();
    expect($result['errors'])->toBeEmpty();
});

test('validates ZIP with template defaults returns success', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'defaults-template',
        'name' => 'Defaults Template',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
        'defaults' => [
            'bride_name' => 'Ayu',
            'groom_name' => 'Raka',
            'gallery' => [
                ['url' => 'assets/gallery/one.jpg', 'caption' => 'One'],
            ],
        ],
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeTrue();
    expect($result['errors'])->toBeEmpty();
});

test('validates ZIP with invalid defaults structure returns error', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'invalid-defaults',
        'name' => 'Invalid Defaults',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
        'defaults' => 'invalid',
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect(implode(' ', $result['errors']))->toContain('"defaults" must be an object/array');
});

test('validates ZIP with invalid defaults key returns error', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'invalid-default-key',
        'name' => 'Invalid Default Key',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
        'defaults' => [
            'bad-key' => 'value',
        ],
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect(implode(' ', $result['errors']))->toContain('Invalid defaults key');
});

test('validates ZIP with missing configured template asset returns error', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'missing-assets',
        'name' => 'Missing Assets',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
        'assets' => [
            'css' => ['assets/missing.css'],
        ],
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect(implode(' ', $result['errors']))->toContain('Missing asset file');
});

test('validates ZIP with unsupported asset file type returns error', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'bad-asset',
        'name' => 'Bad Asset',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->addFromString('assets/hack.php', '<?php echo "bad";');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect(implode(' ', $result['errors']))->toContain('unsupported file type');
});

test('validates ZIP with invalid styling structure returns error', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'test-template',
        'name' => 'Test Template',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
        'styling' => 'invalid', // Should be array/object
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'][0])->toContain('"styling" must be an object/array');
});

test('validates ZIP with invalid features structure returns error', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'test-template',
        'name' => 'Test Template',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
        'features' => 'invalid', // Should be array/object
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'][0])->toContain('"features" must be an object/array');
});

test('validates ZIP with unknown styling keys shows warning', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('template.json', json_encode([
        'slug' => 'test-template',
        'name' => 'Test Template',
        'sections' => [
            ['file' => 'cover.html', 'label' => 'Cover'],
        ],
        'styling' => [
            'unknownKey' => 'value',
        ],
    ]));
    $zip->addFromString('sections/cover.html', '<h1>Cover</h1>');
    $zip->close();

    $result = $this->validator->validate($zipPath);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'][0])->toContain('Unknown styling key');
});

test('isValidTextFile returns false for binary content', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    // Add binary content (null bytes)
    $zip->addFromString('sections/binary.html', "\x00\x01\x02\x03");
    $zip->close();

    $zip->open($zipPath);
    $result = $this->validator->isValidTextFile($zip, 'sections/binary.html');
    $zip->close();

    expect($result)->toBeFalse();
});

test('isValidTextFile returns true for valid HTML', function () {
    $zipPath = $this->tempDir.'/test.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);
    $zip->addFromString('sections/cover.html', '<h1>Valid HTML</h1>');
    $zip->close();

    $zip->open($zipPath);
    $result = $this->validator->isValidTextFile($zip, 'sections/cover.html');
    $zip->close();

    expect($result)->toBeTrue();
});
