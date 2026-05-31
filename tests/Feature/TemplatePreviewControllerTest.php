<?php

use App\Models\Template;
use Illuminate\Support\Facades\File;

afterEach(function () {
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

test('preview API returns HTML with valid data', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create([
        'slug' => $slug,
        'is_active' => true,
    ]);

    // Create template section
    $template->sections()->create([
        'file' => 'cover.html',
        'label' => 'Cover',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    // Create template structure
    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/sections/cover.html', '<h1>{{ $bride_name }} & {{ $groom_name }}</h1>');
    File::put($templatePath.'/assets/style.css', 'body { margin: 0; }');

    $response = $this->postJson("/api/templates/{$slug}/preview", [
        'bride_name' => 'Siti',
        'groom_name' => 'Budi',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['html']);

    $html = $response->json('html');
    expect($html)->toContain('Siti & Budi');
    expect($html)->toContain('Preview Mode');
});

test('preview API returns 404 for non-existent template', function () {
    $response = $this->postJson('/api/templates/non-existent-slug/preview', [
        'bride_name' => 'Test',
    ]);

    $response->assertStatus(404);
});

test('preview API returns 404 for inactive template', function () {
    $template = Template::factory()->create([
        'is_active' => false,
    ]);

    $response = $this->postJson("/api/templates/{$template->slug}/preview", [
        'bride_name' => 'Test',
    ]);

    $response->assertStatus(404);
});

test('preview API merges user data with dummy data', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create([
        'slug' => $slug,
        'is_active' => true,
    ]);

    // Create template section
    $template->sections()->create([
        'file' => 'cover.html',
        'label' => 'Cover',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    // Create template structure
    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/sections/cover.html', '<h1>{{ $bride_name }}</h1><p>{{ $akad_venue }}</p>');
    File::put($templatePath.'/assets/style.css', 'body {}');

    // Send only bride_name, akad_venue should come from dummy data
    $response = $this->postJson("/api/templates/{$slug}/preview", [
        'bride_name' => 'Custom Name',
    ]);

    $response->assertStatus(200);

    $html = $response->json('html');
    expect($html)->toContain('Custom Name'); // User data
    expect($html)->toContain('Masjid'); // From dummy data
});

test('preview API works without authentication', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create([
        'slug' => $slug,
        'is_active' => true,
    ]);

    // Create template structure
    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/sections/cover.html', '<h1>Test</h1>');
    File::put($templatePath.'/assets/style.css', 'body {}');

    // No authentication
    $response = $this->postJson("/api/templates/{$slug}/preview", [
        'bride_name' => 'Test',
    ]);

    $response->assertStatus(200);
});

test('preview API includes preview banner in HTML', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create([
        'slug' => $slug,
        'name' => 'Test Template',
        'is_active' => true,
    ]);

    // Create template structure
    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/sections/cover.html', '<h1>Test</h1>');
    File::put($templatePath.'/assets/style.css', 'body {}');

    $response = $this->postJson("/api/templates/{$slug}/preview", []);

    $response->assertStatus(200);

    $html = $response->json('html');
    expect($html)->toContain('Preview Mode');
    expect($html)->toContain('Test Template');
});
