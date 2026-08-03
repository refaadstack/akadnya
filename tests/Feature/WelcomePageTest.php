<?php

use App\Models\Template;

test('welcome page shows active featured templates from database thumbnails', function () {
    Template::factory()->create([
        'name' => 'Zulu Inactive',
        'slug' => 'zulu-inactive',
        'thumbnail_url' => 'inactive.jpg',
        'is_active' => false,
    ]);

    $paidTemplate = Template::factory()->create([
        'name' => 'Beta Paid',
        'slug' => 'beta-paid',
        'thumbnail_url' => 'templates/beta/thumb.jpg',
        'is_free' => false,
        'price' => 150000,
        'is_active' => true,
    ]);

    $freeTemplate = Template::factory()->free()->create([
        'name' => 'Alpha Free',
        'slug' => 'alpha-free',
        'thumbnail_url' => 'https://example.com/alpha.jpg',
        'is_active' => true,
    ]);

    foreach (['Charlie Paid', 'Delta Paid', 'Echo Paid'] as $name) {
        Template::factory()->create([
            'name' => $name,
            'is_free' => false,
            'is_active' => true,
        ]);
    }

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Welcome')
        ->has('featuredTemplates', 3)
        ->where('featuredTemplates.0.id', $freeTemplate->id)
        ->where('featuredTemplates.0.thumbnail_url', 'https://example.com/alpha.jpg')
        ->where('featuredTemplates.1.id', $paidTemplate->id)
        ->where('featuredTemplates.1.thumbnail_url', asset('storage/templates/beta/thumb.jpg'))
        ->missing('featuredTemplates.3')
    );
});

test('welcome page shows cheapest paid template as starting price', function () {
    Template::factory()->create([
        'name' => 'Expensive',
        'is_free' => false,
        'price' => 250000,
        'is_active' => true,
    ]);
    Template::factory()->create([
        'name' => 'Cheapest',
        'is_free' => false,
        'price' => 149000,
        'original_price' => 199000,
        'is_active' => true,
    ]);
    Template::factory()->free()->create(['is_active' => true]);

    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('startingTemplate.name', 'Cheapest')
        ->where('startingTemplate.price', '149000.00')
        ->where('startingTemplate.original_price', '199000.00')
        ->where('startingTemplate.discount_percent', 25)
    );
});
