<?php

use App\Models\Invitation;
use App\Models\InvitationContent;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Carbon;
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

test('published invitation page includes seo meta tags with couple names and akadnya.com branding', function () {
    $user = User::factory()->create();
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $templateSection = $template->sections()->create([
        'file' => 'hero.html',
        'label' => 'Hero',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::put($templatePath.'/sections/hero.html', '<section class="seo-hero">{{ $bride_name ?? "" }}</section>');

    $invitation = Invitation::factory()->published()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Nadia',
        'groom_name' => 'Raka',
        'akad_datetime' => '2026-06-14 09:00:00',
        'akad_venue' => 'Gedung Merdeka',
        'cover_photo_url' => 'https://example.com/storage/invitations/covers/cover.jpg',
    ]);
    $invitation->sections()->create([
        'template_section_id' => $templateSection->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $response = $this->get('/i/'.$invitation->subdomain);

    $response->assertOk();

    $siteName = config('app.name', 'Akadnya.com');
    $response->assertSee("<title>Undangan Pernikahan Nadia &amp; Raka | {$siteName}</title>", false);
    $response->assertDontSee('<title>Nadia & Raka</title>', false);

    $formattedDate = Carbon::parse('2026-06-14 09:00:00')->locale('id')->isoFormat('dddd, D MMMM YYYY');
    $response->assertSee('Undangan digital pernikahan Nadia &amp; Raka — akad '.$formattedDate.' — di Gedung Merdeka — dibuat dengan '.$siteName, false);

    $response->assertSee('<link rel="canonical" href="'.rtrim(config('app.url'), '/').'/i/'.$invitation->subdomain.'"', false);
    $response->assertSee('<meta property="og:url"', false);
    $response->assertSee('<meta property="og:title"', false);
    $response->assertSee('<meta property="og:site_name" content="'.$siteName.'"', false);
    // No usable local photo file in this test, so OG falls back to a Akadnya.com
    // branded placeholder hosted at the configured app URL (never localhost/favicon).
    $response->assertSee('<meta property="og:image" content="'.rtrim(config('app.url'), '/').'/images/placeholder-template.png"', false);
    $response->assertDontSee('favicon', false);
    $response->assertSee('<meta name="twitter:card" content="summary_large_image"', false);
    $response->assertSee('<meta name="robots" content="index, follow"', false);

    $response->assertSee('application/ld+json', false);
    $response->assertSee('"@type":"Event"', false);
    $response->assertSee('"startDate":"2026-06-14T09:00:00', false);
    $response->assertSee('"organizer"', false);
    $response->assertSee('"name":"'.$siteName.'"', false);
});

test('single-file template page gets seo tags injected into its own head', function () {
    $user = User::factory()->create();
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $template->sections()->create([
        'file' => 'full.html',
        'label' => 'Full Page',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::put($templatePath.'/sections/full.html', '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Custom Title Lama</title></head><body class="standalone-seo"><h1>Budi &amp; Sari</h1></body></html>');
    File::put($templatePath.'/template.json', json_encode([
        'name' => 'Single File',
        'slug' => $slug,
        'version' => '1.0.0',
        'single_file' => true,
        'sections' => [
            ['file' => 'full.html', 'label' => 'Full Page'],
        ],
        'assets' => [],
    ]));

    $invitation = Invitation::factory()->published()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Budi',
        'groom_name' => 'Sari',
    ]);

    $response = $this->get('/i/'.$invitation->subdomain);

    $response->assertOk();
    $response->assertSee('<title>Undangan Pernikahan Budi &amp; Sari | '.config('app.name', 'Akadnya.com').'</title>', false);
    $response->assertDontSee('<title>Custom Title Lama</title>', false);
    $response->assertSee('<meta name="description"', false);
    $response->assertSee('<link rel="canonical"', false);
    $response->assertSee('application/ld+json', false);
    $response->assertSee('<h1>Budi &amp; Sari</h1>', false);
});

test('seo falls back to generic title when couple names are missing', function () {
    $user = User::factory()->create();
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $templateSection = $template->sections()->create([
        'file' => 'hero.html',
        'label' => 'Hero',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::put($templatePath.'/sections/hero.html', '<section class="seo-hero">empty</section>');

    $invitation = Invitation::factory()->published()->for($user)->for($template)->create();
    InvitationContent::create(['invitation_id' => $invitation->id]);
    $invitation->sections()->create([
        'template_section_id' => $templateSection->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $response = $this->get('/i/'.$invitation->subdomain);

    $response->assertOk();
    $response->assertSee('<title>Undangan Digital | '.config('app.name', 'Akadnya.com').'</title>', false);
});
