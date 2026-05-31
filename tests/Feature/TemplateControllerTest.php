<?php

use App\Models\Template;
use Illuminate\Support\Facades\File;

afterEach(function () {
    $templatesPath = storage_path('app/public/templates');

    if (is_dir($templatesPath)) {
        foreach (glob($templatesPath.'/test-*') as $dir) {
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }
    }
});

test('preview redirects to standalone render route', function () {
    $template = Template::factory()->create([
        'slug' => 'elegant-wedding',
        'name' => 'Elegant Wedding',
        'price' => 150000,
        'is_active' => true,
    ]);

    $response = $this->get("/templates/{$template->slug}/preview");

    $response->assertRedirect("/templates/{$template->slug}/render");
});

test('preview redirect preserves custom render data query', function () {
    $template = Template::factory()->create([
        'slug' => 'query-template',
        'is_active' => true,
    ]);

    $response = $this->get("/templates/{$template->slug}/preview?data=abc123");

    $response->assertRedirect("/templates/{$template->slug}/render?data=abc123");
});

test('preview returns 404 for inactive template', function () {
    $template = Template::factory()->create([
        'slug' => 'inactive-template',
        'is_active' => false,
    ]);

    $response = $this->get("/templates/{$template->slug}/preview");

    $response->assertNotFound();
});

test('preview returns 404 for non-existent template', function () {
    $response = $this->get('/templates/non-existent/preview');

    $response->assertNotFound();
});

test('render returns standalone template preview html with template assets', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create([
        'slug' => $slug,
        'name' => 'Standalone Template',
        'is_active' => true,
    ]);

    $template->sections()->create([
        'file' => 'hero.html',
        'label' => 'Hero',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/template.json', json_encode([
        'defaults' => [
            'bride_name' => 'Template Bride',
        ],
    ]));
    File::put($templatePath.'/sections/hero.html', '<h1>{{ $bride_name ?? "" }}</h1>');
    File::put($templatePath.'/assets/style.css', 'h1 { color: red; }');
    File::put($templatePath.'/assets/script.js', 'window.loaded = true;');

    $response = $this->get("/templates/{$slug}/render");

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
    $response->assertSee('<!DOCTYPE html>', false);
    $response->assertSee('Preview Mode - Template: Standalone Template', false);
    $response->assertSee('Template Bride', false);
    $response->assertSee("template-assets/{$slug}/style.css", false);
    $response->assertSee("template-assets/{$slug}/script.js", false);
    $response->assertDontSee('template-base.css', false);
    $response->assertDontSee('template-base.js', false);
});

test('template asset route serves safe assets and blocks traversal', function () {
    $slug = 'test-'.uniqid();
    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/assets/css', 0755, true);
    File::put($templatePath.'/assets/css/theme.css', 'body { color: red; }');

    $this->get("/template-assets/{$slug}/css/theme.css")
        ->assertOk()
        ->assertHeader('Content-Type', 'text/css; charset=UTF-8');

    $this->get("/template-assets/{$slug}/../template.json")
        ->assertNotFound();
});
