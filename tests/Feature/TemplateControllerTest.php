<?php

use App\Models\Template;
use Inertia\Testing\AssertableInertia as Assert;

test('preview returns correct template metadata', function () {
    $template = Template::factory()->create([
        'slug' => 'elegant-wedding',
        'name' => 'Elegant Wedding',
        'price' => 150000,
        'is_active' => true,
    ]);

    $response = $this->get("/templates/{$template->slug}/preview");

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Templates/Preview')
        ->has('template', fn (Assert $prop) => $prop
            ->where('id', $template->id)
            ->where('slug', 'elegant-wedding')
            ->where('name', 'Elegant Wedding')
            ->where('price', '150000.00')
            ->missing('sections')
            ->missing('ornaments')
            ->missing('dummyData')
        )
    );
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
