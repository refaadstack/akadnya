<?php

use App\Models\Invitation;
use App\Models\InvitationContent;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\File;

test('favicon bundle exists, is lightweight and brand versioned', function () {
    expect(public_path('favicon.ico'))->toBeFile();
    expect(public_path('favicon.svg'))->toBeFile();
    expect(public_path('apple-touch-icon.png'))->toBeFile();
    expect(public_path('site.webmanifest'))->toBeFile();

    // SVG must be a tiny vector, never a base64-embedded PNG (was 342KB).
    $svg = File::get(public_path('favicon.svg'));
    expect(strlen($svg))->toBeLessThan(5 * 1024);
    expect($svg)->not->toContain('base64')
        ->and($svg)->not->toContain('RealFaviconGenerator');

    $manifest = json_decode(File::get(public_path('site.webmanifest')), true);
    expect($manifest)->toBeArray()
        ->and($manifest['name'] ?? null)->not->toBeEmpty();
    $purposes = collect($manifest['icons'] ?? [])->pluck('purpose')->implode(' ');
    expect($purposes)->toContain('any');
});

test('welcome page references versioned brand favicon', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('favicon.ico?v=', false);
    $response->assertSee('favicon.svg?v=', false);
    $response->assertSee('apple-touch-icon.png?v=', false);
    $response->assertSee('site.webmanifest', false);
});

test('public invitation html includes versioned brand favicon', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['slug' => 'fav-'.uniqid()]);
    $template->sections()->create([
        'file' => 'hero.html',
        'label' => 'Hero',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $templatePath = storage_path("app/public/templates/{$template->slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::put($templatePath.'/sections/hero.html', '<section>hello</section>');

    try {
        $invitation = Invitation::factory()->published()->for($user)->for($template)->create();
        InvitationContent::create([
            'invitation_id' => $invitation->id,
            'bride_name' => 'Ayu',
            'groom_name' => 'Bimo',
        ]);
        $invitation->sections()->create([
            'template_section_id' => $template->sections()->first()->id,
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        $response = $this->get('/i/'.$invitation->subdomain);
        $response->assertOk();
        $response->assertSee('favicon.svg?v=', false);
        $response->assertSee('favicon.ico?v=', false);
    } finally {
        File::deleteDirectory($templatePath);
    }
});
