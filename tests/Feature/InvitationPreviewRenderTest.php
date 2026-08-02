<?php

use App\Http\Middleware\HasBasePackage;
use App\Models\Invitation;
use App\Models\InvitationContent;
use App\Models\LoveStory;
use App\Models\Rsvp;
use App\Models\Template;
use App\Models\User;
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
    $this->withoutMiddleware(HasBasePackage::class);

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
    $this->withoutMiddleware(HasBasePackage::class);

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
    $this->withoutMiddleware(HasBasePackage::class);

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
