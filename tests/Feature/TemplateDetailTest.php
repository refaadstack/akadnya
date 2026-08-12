<?php

use App\Models\Template;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

afterEach(function () {
    $screenshotsPath = storage_path('app/public/templates/shots');

    if (is_dir($screenshotsPath)) {
        foreach (glob($screenshotsPath.'/test-*') as $dir) {
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }
    }
});

test('template detail page renders publicly with template data', function () {
    $template = Template::factory()->create([
        'slug' => 'show-template',
        'name' => 'Show Template',
        'price' => 149000,
        'is_active' => true,
    ]);

    $response = $this->get("/templates/{$template->slug}");

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Templates/Show')
            ->where('template.id', $template->id)
            ->where('template.name', 'Show Template')
            ->where('template.slug', 'show-template')
            ->where('template.price', 149000)
            ->where('template.description', '')
            ->where('template.screenshots', [])
            ->has('preview_defaults'));
});

test('template detail page exposes template description and screenshots', function () {
    $slug = 'test-show-shots';
    $template = Template::factory()->create([
        'slug' => $slug,
        'is_active' => true,
    ]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath, 0755, true);
    File::put($templatePath.'/template.json', json_encode([
        'description' => 'Deskripsi template warisan budaya.',
    ]));

    $shotsDir = storage_path("app/public/templates/shots/{$slug}");
    File::makeDirectory($shotsDir, 0755, true);
    File::put($shotsDir.'/mobile-cover.png', 'png');
    File::put($shotsDir.'/desktop.png', 'png');

    $response = $this->get("/templates/{$slug}");

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('template.description', 'Deskripsi template warisan budaya.')
            ->where('template.screenshots.0', asset("storage/templates/shots/{$slug}/mobile-cover.png"))
            ->where('template.screenshots.1', asset("storage/templates/shots/{$slug}/desktop.png"))
            ->count('template.screenshots', 2));
});

test('template detail page exposes template preview defaults', function () {
    $slug = 'test-show-defaults';
    $template = Template::factory()->create([
        'slug' => $slug,
        'is_active' => true,
    ]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath, 0755, true);
    File::put($templatePath.'/template.json', json_encode([
        'defaults' => [
            'groom_name' => 'Sultan Ibrahim',
            'bride_name' => 'Andi Tenri',
        ],
    ]));

    $response = $this->get("/templates/{$slug}");

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('preview_defaults.groom_name', 'Sultan Ibrahim')
            ->where('preview_defaults.bride_name', 'Andi Tenri'));
});

test('template detail page returns 404 for non-existent template', function () {
    $this->get('/templates/non-existent')
        ->assertNotFound();
});
