<?php

use App\Http\Middleware\HasInvitationAccess;
use App\Models\Invitation;
use App\Models\InvitationContent;
use App\Models\LoveStory;
use App\Models\Template;
use App\Models\User;
use App\Services\BladeRenderService;
use App\Services\DataContractBuilder;
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

test('authenticated client preview returns standalone rendered invitation html', function () {
    $this->withoutMiddleware(HasInvitationAccess::class);

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
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/sections/hero.html', '<section class="client-preview">{{ $bride_name ?? "" }} & {{ $groom_name ?? "" }}</section>');
    File::put($templatePath.'/assets/style.css', '.client-preview { color: red; }');
    File::put($templatePath.'/assets/script.js', 'window.templatePreviewLoaded = true;');

    $invitation = Invitation::factory()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Nadia',
        'groom_name' => 'Raka',
    ]);

    $invitation->sections()->create([
        'template_section_id' => $templateSection->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $response = $this->actingAs($user)->get('/dashboard/editor/preview');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
    $response->assertSee('<!DOCTYPE html>', false);
    $response->assertSee('Nadia & Raka', false);
    $response->assertSee("template-assets/{$slug}/style.css", false);
    $response->assertSee("template-assets/{$slug}/script.js", false);
});

test('client preview renders love stories and wishes from database', function () {
    $this->withoutMiddleware(HasInvitationAccess::class);

    $user = User::factory()->create();
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $storySection = $template->sections()->create([
        'file' => 'story.html',
        'label' => 'Story',
        'sort_order' => 1,
        'is_required' => true,
    ]);
    $rsvpSection = $template->sections()->create([
        'file' => 'rsvp.html',
        'label' => 'RSVP',
        'sort_order' => 2,
        'is_required' => true,
    ]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/sections/story.html', '<ul class="story-list">@foreach($love_stories ?? [] as $story)<li class="story-item">{{ $story["date"] ?? "" }}|{{ $story["title"] ?? "" }}|{{ $story["description"] ?? "" }}</li>@endforeach</ul>');
    File::put($templatePath.'/sections/rsvp.html', '<ul class="wish-list">@foreach($wishes ?? [] as $wish)<li class="wish-item">{{ $wish["name"] ?? "" }}|{{ $wish["message"] ?? "" }}</li>@endforeach</ul>');
    File::put($templatePath.'/assets/style.css', '');
    File::put($templatePath.'/assets/script.js', '');

    $invitation = Invitation::factory()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Nadia',
        'groom_name' => 'Raka',
    ]);

    LoveStory::factory()->for($invitation)->create([
        'title' => 'Pertama Bertemu',
        'date_label' => 'Januari 2020',
        'description' => 'Kami bertemu di kampus.',
        'sort_order' => 0,
    ]);

    $invitation->rsvps()->create([
        'name' => 'Budi',
        'attendance' => 'yes',
        'pax_count' => 2,
        'message' => 'Selamat menempuh hidup baru!',
    ]);

    $invitation->sections()->create([
        'template_section_id' => $storySection->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);
    $invitation->sections()->create([
        'template_section_id' => $rsvpSection->id,
        'sort_order' => 2,
        'is_visible' => true,
    ]);

    $response = $this->actingAs($user)->get('/dashboard/editor/preview');

    $response->assertOk();
    $response->assertSee('Januari 2020|Pertama Bertemu|Kami bertemu di kampus.', false);
    $response->assertSee('Budi|Selamat menempuh hidup baru!', false);
    $response->assertDontSee('Pertemuan Pertama</h4>', false);
});

test('client preview cover renders initials from nicknames', function () {
    $this->withoutMiddleware(HasInvitationAccess::class);

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
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/sections/hero.html', '<h1 class="cover-title">{{ $couple_initials ?? "" }}</h1>');
    File::put($templatePath.'/assets/style.css', '');
    File::put($templatePath.'/assets/script.js', '');

    $invitation = Invitation::factory()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Ayu Nadia Putri',
        'bride_nickname' => 'Ayu',
        'groom_name' => 'Raka Pradana',
        'groom_nickname' => 'Raka',
    ]);

    $invitation->sections()->create([
        'template_section_id' => $templateSection->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $response = $this->actingAs($user)->get('/dashboard/editor/preview');

    $response->assertOk();
    $response->assertSee('<h1 class="cover-title">R &amp; A</h1>', false);
    $response->assertDontSee('class="cover-title">Ayu Nadia Putri', false);
});

test('single-file template renders as standalone html without wrapper', function () {
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
    File::put($templatePath.'/sections/full.html', '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>{{ $bride_name }} & {{ $groom_name }}</title></head><body class="standalone-page"><h1 id="cover-name">{{ $couple_initials ?? "" }}</h1></body></html>');
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

    $invitation = Invitation::factory()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Nadia Putri',
        'groom_name' => 'Raka Pradana',
        'bride_nickname' => 'Nadia',
        'groom_nickname' => 'Raka',
    ]);

    $data = (new DataContractBuilder)->build($invitation);
    $html = (new BladeRenderService)->renderInvitation($invitation, $data);

    expect($html)->toStartWith('<!DOCTYPE html>');
    expect($html)->toContain('<title>Nadia Putri & Raka Pradana</title>');
    expect($html)->toContain('<h1 id="cover-name">R &amp; N</h1>');
    expect($html)->toContain('standalone-page');
    expect($html)->not->toContain("template-{$slug}");
    expect($html)->not->toContain('<meta name="csrf-token"');
});

test('rendered template embeds background url from invitation content', function () {
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
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/sections/hero.html', '@if($background_url ?? null)<style>body { background: url("{{ $background_url }}") center/cover no-repeat fixed; }</style>@endif<section class="bg-hero">{{ $bride_name ?? "" }}</section>');
    File::put($templatePath.'/assets/style.css', '.bg-hero { color: red; }');
    File::put($templatePath.'/assets/script.js', '');

    $invitation = Invitation::factory()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Nadia',
        'groom_name' => 'Raka',
        'background_url' => 'https://example.com/storage/invitations/backgrounds/bg.jpg',
    ]);
    $invitation->sections()->create([
        'template_section_id' => $templateSection->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $data = (new DataContractBuilder)->build($invitation);
    $html = (new BladeRenderService)->renderInvitation($invitation, $data);

    expect($html)->toContain('url("/storage/invitations/backgrounds/bg.jpg")');
});

test('rendered template omits background block when background url is empty', function () {
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
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/sections/hero.html', '@if($background_url ?? null)<style>body { background: url("{{ $background_url }}"); }</style>@endif<section class="bg-hero">{{ $bride_name ?? "" }}</section>');
    File::put($templatePath.'/assets/style.css', '');
    File::put($templatePath.'/assets/script.js', '');

    $invitation = Invitation::factory()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Nadia',
        'groom_name' => 'Raka',
    ]);
    $invitation->sections()->create([
        'template_section_id' => $templateSection->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $data = (new DataContractBuilder)->build($invitation);
    $html = (new BladeRenderService)->renderInvitation($invitation, $data);

    expect($html)->not->toContain('background: url("');
});

test('live invitation with no love story suppresses the hardcoded placeholder fallback', function () {
    $user = User::factory()->create();
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $storySection = $template->sections()->create([
        'file' => 'story.html',
        'label' => 'Story',
        'sort_order' => 1,
        'is_required' => false,
    ]);

    // No local story.html -> falls back to the shared _shared/sections/story.html
    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/assets/style.css', '');
    File::put($templatePath.'/assets/script.js', '');

    $invitation = Invitation::factory()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Nadia',
        'groom_name' => 'Raka',
    ]);
    // No love stories and no love_story text
    $invitation->sections()->create([
        'template_section_id' => $storySection->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $data = (new DataContractBuilder)->build($invitation);
    $html = (new BladeRenderService)->renderInvitation($invitation, $data);

    expect($html)->not->toContain('Pertemuan Pertama');
    expect($html)->not->toContain('Restu Keluarga');
    expect($html)->not->toContain('Pernikahan');
    // The section stays and shows a neutral "story will load" placeholder
    expect($html)->toContain('id="iv-story"');
    expect($html)->toContain('Cerita kami akan dimuat di sini.');
});

test('preview with no love story still shows the hardcoded placeholder fallback', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $template->sections()->create([
        'file' => 'story.html',
        'label' => 'Story',
        'sort_order' => 1,
        'is_required' => false,
    ]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::makeDirectory($templatePath.'/assets', 0755, true);
    File::put($templatePath.'/assets/style.css', '');
    File::put($templatePath.'/assets/script.js', '');

    $data = (new DataContractBuilder)->buildTemplateDefaults($template);
    $html = (new BladeRenderService)->renderPreview($template, $data);

    expect($html)->toContain('Pertemuan Pertama');
    expect($html)->toContain('Restu Keluarga');
    expect($html)->toContain('Pernikahan');
});
