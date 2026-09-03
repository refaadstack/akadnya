<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationContent;
use App\Models\InvitationGallery;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Template;
use App\Models\User;
use App\Models\UserGrant;
use App\Services\DataContractBuilder;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->builder = new DataContractBuilder;
    SiteSetting::flush();
});

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

test('build returns all data contract keys even with null content', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    // No content created - all fields should be null
    $contract = $this->builder->build($invitation);

    // Verify all required keys exist
    expect($contract)->toHaveKeys([
        'bride_name',
        'bride_nickname',
        'bride_father',
        'bride_mother',
        'bride_photo_url',
        'bride_initial',
        'groom_name',
        'groom_nickname',
        'groom_father',
        'groom_mother',
        'groom_photo_url',
        'groom_initial',
        'couple_photo_url',
        'couple_initials',
        'akad_venue',
        'akad_maps_url',
        'akad_datetime_formatted',
        'akad_time',
        'akad_date',
        'akad_month',
        'akad_year',
        'akad_day',
        'reception_venue',
        'reception_maps_url',
        'reception_datetime_formatted',
        'reception_time',
        'reception_date',
        'reception_month',
        'reception_year',
        'reception_day',
        'cover_photo_url',
        'video_url',
        'video_youtube_id',
        'background_url',
        'music_url',
        'music_title',
        'love_story',
        'special_message',
        'gallery',
        'love_stories',
        'wishes',
        'rsvp_action',
        'csrf_token',
        'guest_name',
        'show_reception',
        'akad_datetime',
        'event_date',
    ]);

    // All values should be null except gallery (empty array), rsvp_action, and csrf_token
    expect($contract['bride_name'])->toBeNull();
    expect($contract['gallery'])->toBeArray()->toBeEmpty();
    expect($contract['love_stories'])->toBeArray()->toBeEmpty();
    expect($contract['wishes'])->toBeArray()->toBeEmpty();
    expect($contract['rsvp_action'])->toBeString();
    expect($contract['csrf_token'])->toBeString();
});

test('build populates data from invitation content', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Siti',
        'groom_name' => 'Budi',
        'akad_datetime' => Carbon::parse('2025-06-14 09:00:00'),
        'akad_venue' => 'Masjid Al-Ikhlas',
        'background_url' => 'https://example.com/bg.jpg',
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['bride_name'])->toBe('Siti');
    expect($contract['groom_name'])->toBe('Budi');
    expect($contract['akad_venue'])->toBe('Masjid Al-Ikhlas');
    expect($contract['akad_datetime_formatted'])->toBeString();
    expect($contract['akad_time'])->toContain('WIB');
    expect($contract['background_url'])->toBe('https://example.com/bg.jpg');
});

test('buildDatetimeVariables generates consistent datetime variables', function () {
    $datetime = Carbon::parse('2025-06-14 09:00:00');

    $variables = $this->builder->buildDatetimeVariables('akad', $datetime);

    expect($variables)->toHaveKeys([
        'akad_datetime_formatted',
        'akad_time',
        'akad_date',
        'akad_month',
        'akad_year',
        'akad_day',
    ]);

    expect($variables['akad_year'])->toBe('2025');
    expect($variables['akad_date'])->toBe('14');
    expect($variables['akad_time'])->toContain('09:00');
});

test('buildDatetimeVariables returns null values when datetime is null', function () {
    $variables = $this->builder->buildDatetimeVariables('akad', null);

    expect($variables['akad_datetime_formatted'])->toBeNull();
    expect($variables['akad_time'])->toBeNull();
    expect($variables['akad_date'])->toBeNull();
});

test('build includes gallery array with correct structure', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    InvitationGallery::create([
        'invitation_id' => $invitation->id,
        'image_url' => 'https://example.com/photo1.jpg',
        'caption' => 'Photo 1',
        'sort_order' => 1,
    ]);

    InvitationGallery::create([
        'invitation_id' => $invitation->id,
        'image_url' => 'https://example.com/photo2.jpg',
        'caption' => 'Photo 2',
        'sort_order' => 2,
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['gallery'])->toHaveCount(2);
    expect($contract['gallery'][0])->toHaveKeys(['url', 'caption']);
    expect($contract['gallery'][0]['url'])->toBe('https://example.com/photo1.jpg');
    expect($contract['gallery'][0]['caption'])->toBe('Photo 1');
});

test('build includes love stories with correct structure', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $invitation->loveStories()->create([
        'title' => 'Pertama Bertemu',
        'date_label' => 'Januari 2020',
        'description' => 'Kami bertemu di kampus.',
        'sort_order' => 0,
    ]);

    $invitation->loveStories()->create([
        'title' => 'Lamaran',
        'date_label' => null,
        'description' => null,
        'sort_order' => 1,
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['love_stories'])->toHaveCount(2);
    expect($contract['love_stories'][0])->toHaveKeys(['date', 'title', 'description']);
    expect($contract['love_stories'][0]['date'])->toBe('Januari 2020');
    expect($contract['love_stories'][0]['title'])->toBe('Pertama Bertemu');
    expect($contract['love_stories'][1]['date'])->toBeNull();
});

test('build extracts youtube id from video url', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['video_youtube_id'])->toBe('dQw4w9WgXcQ');
});

test('build resolves null youtube id for non-youtube video url', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'video_url' => 'https://example.com/video.mp4',
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['video_youtube_id'])->toBeNull();
});

test('build includes couple photo, music title and gift address', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'couple_photo_url' => 'https://example.com/couple.jpg',
        'music_title' => 'Sepanjang Hidup - Maher Zain',
        'gift_address' => 'Jl. Melati No. 12, Jambi',
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['couple_photo_url'])->toBe('https://example.com/couple.jpg');
    expect($contract['music_title'])->toBe('Sepanjang Hidup - Maher Zain');
    expect($contract['gift_address'])->toBe('Jl. Melati No. 12, Jambi');
});

test('build includes wishes with correct structure', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $invitation->rsvps()->create([
        'name' => 'Budi',
        'attendance' => 'yes',
        'message' => 'Selamat ya',
        'pax_count' => 2,
    ]);
    $invitation->rsvps()->create([
        'name' => 'Siti',
        'attendance' => 'pending',
        'message' => null,
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['wishes'])->toHaveCount(1);
    expect($contract['wishes'][0])->toHaveKeys(['id', 'name', 'message', 'attendance', 'created_at']);
    expect($contract['wishes'][0]['name'])->toBe('Budi');
    expect($contract['wishes'][0]['message'])->toBe('Selamat ya');
});

test('build includes akad datetime in ISO format', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $akad = Carbon::parse('2026-12-25 09:00:00');

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'akad_datetime' => $akad,
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['akad_datetime'])->toBe($akad->toIso8601String());
});

test('build defaults show_reception to true when flag is unset', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['show_reception'])->toBeTrue();
});

test('build falls back event_date to akad when reception is hidden', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $akad = Carbon::parse('2026-12-25 09:00:00');
    $reception = Carbon::parse('2026-12-26 11:00:00');

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'akad_datetime' => $akad,
        'reception_datetime' => $reception,
        'show_reception' => false,
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['show_reception'])->toBeFalse();
    expect($contract['event_date'])->toBe($akad->toIso8601String());
});

test('build falls back event_date to akad when reception datetime is empty', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $akad = Carbon::parse('2026-12-25 09:00:00');

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'akad_datetime' => $akad,
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['event_date'])->toBe($akad->toIso8601String());
});

test('build uses reception for event_date when reception is shown', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $akad = Carbon::parse('2026-12-25 09:00:00');
    $reception = Carbon::parse('2026-12-26 11:00:00');

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'akad_datetime' => $akad,
        'reception_datetime' => $reception,
        'show_reception' => true,
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['event_date'])->toBe($reception->toIso8601String());
});

test('build includes nicknames and initials derived from nicknames', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'groom_name' => 'Redho Fadillah Adha',
        'groom_nickname' => 'Redho',
        'bride_name' => 'Yeliani Putri Tandyana',
        'bride_nickname' => 'Yeli',
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['groom_nickname'])->toBe('Redho');
    expect($contract['bride_nickname'])->toBe('Yeli');
    expect($contract['groom_initial'])->toBe('R');
    expect($contract['bride_initial'])->toBe('Y');
    expect($contract['couple_initials'])->toBe('R & Y');
    expect($contract['cover_name_display'])->toBe('full');
    expect($contract['cover_names'])->toBe('Yeliani Putri Tandyana & Redho Fadillah Adha');
});

test('build computes cover names from nickname and initials display modes', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'groom_name' => 'Redho Fadillah Adha',
        'groom_nickname' => 'Redho',
        'bride_name' => 'Yeliani Putri Tandyana',
        'bride_nickname' => 'Yeli',
        'cover_name_display' => 'nickname',
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['cover_names'])->toBe('Yeli & Redho');

    $invitation->content->update(['cover_name_display' => 'initials']);
    $contract = $this->builder->build($invitation);

    expect($contract['cover_names'])->toBe('Y & R');
    expect($contract['cover_name_display'])->toBe('initials');
});

test('build falls back to full name initials when nickname is empty', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'groom_name' => 'Redho Fadillah Adha',
        'bride_name' => 'Yeliani Putri Tandyana',
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['groom_nickname'])->toBeNull();
    expect($contract['groom_initial'])->toBe('R');
    expect($contract['bride_initial'])->toBe('Y');
    expect($contract['couple_initials'])->toBe('R & Y');
});

test('buildTemplateDefaults computes initials from template defaults', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $templatePath = storage_path("app/public/templates/{$slug}");

    File::makeDirectory($templatePath, 0755, true);
    File::put($templatePath.'/template.json', json_encode([
        'defaults' => [
            'bride_name' => 'Ayu Template',
            'bride_nickname' => 'Ayu',
            'groom_name' => 'Raka Template',
            'groom_nickname' => 'Raka',
        ],
    ]));

    $contract = $this->builder->buildTemplateDefaults($template);

    expect($contract['groom_initial'])->toBe('R');
    expect($contract['bride_initial'])->toBe('A');
    expect($contract['couple_initials'])->toBe('R & A');
});

test('buildTemplateDefaults returns template-owned preview data', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $templatePath = storage_path("app/public/templates/{$slug}");

    File::makeDirectory($templatePath, 0755, true);
    File::put($templatePath.'/template.json', json_encode([
        'defaults' => [
            'bride_name' => 'Ayu Template',
            'groom_name' => 'Raka Template',
            'akad_datetime' => '2026-08-17 09:00:00',
            'gallery' => [
                ['url' => 'assets/gallery/one.jpg', 'caption' => 'One'],
            ],
        ],
    ]));

    $contract = $this->builder->buildTemplateDefaults($template);

    expect($contract)->toHaveKeys([
        'bride_name',
        'groom_name',
        'akad_venue',
        'reception_venue',
        'gallery',
    ]);

    expect($contract['bride_name'])->toBe('Ayu Template');
    expect($contract['groom_name'])->toBe('Raka Template');
    expect($contract['gallery'])->toBeArray()->not->toBeEmpty();
    expect($contract['akad_datetime_formatted'])->toBeString();
    expect($contract['reception_datetime_formatted'])->toBeNull();
});

test('build includes guest book data when feature enabled and valid guest code', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'type' => 'addon',
        'slug' => 'guest_book',
    ]);
    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($user, null, $product);
    $orderService->updateOrderStatus($order, 'paid');

    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $guest = Guest::create([
        'invitation_id' => $invitation->id,
        'name' => 'Budi Santoso',
        'category' => 'family',
        'max_pax' => 2,
    ]);

    $contract = $this->builder->build($invitation, null, $guest->unique_code);

    expect($contract['guest_book_enabled'])->toBeTrue();
    expect($contract['guest'])->toHaveKeys(['id', 'name', 'unique_code', 'category', 'max_pax']);
    expect($contract['guest']['name'])->toBe('Budi Santoso');
    expect($contract['guest']['unique_code'])->toBe($guest->unique_code);
    expect($contract['guest_qr_svg'])->toBeString()->toContain('<svg');
    expect($contract['guest_qr_svg'])->toContain('<image');
    expect($contract['guest_qr_svg'])->toContain('data:image/svg+xml;base64,');
    expect($contract['guest_qr_svg'])->toContain('xmlns:xlink="http://www.w3.org/1999/xlink"');
    expect($contract['guest_qr_svg'])->toContain('width="180" height="180"');
    expect($contract['guest_qr_svg'])->toContain('<rect x="0" y="0" width="300" height="300" fill="#ffffff"/>');
});

test('build embeds the configured QR logo from site settings', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'type' => 'addon',
        'slug' => 'guest_book',
    ]);
    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($user, null, $product);
    $orderService->updateOrderStatus($order, 'paid');

    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $guest = Guest::create([
        'invitation_id' => $invitation->id,
        'name' => 'Budi Santoso',
        'category' => 'family',
        'max_pax' => 2,
    ]);

    SiteSetting::set('qr_logo_url', 'https://example.com/storage/branding/custom-logo.svg');

    $contract = $this->builder->build($invitation, null, $guest->unique_code);

    expect($contract['guest_qr_svg'])->toContain('https://example.com/storage/branding/custom-logo.svg');
    expect($contract['guest_qr_svg'])->not->toContain('/favicon.svg');
});

test('build omits guest book data when feature is not enabled', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $guest = Guest::create([
        'invitation_id' => $invitation->id,
        'name' => 'Budi Santoso',
    ]);

    $contract = $this->builder->build($invitation, null, $guest->unique_code);

    expect($contract['guest_book_enabled'])->toBeFalse();
    expect($contract['guest'])->toBeNull();
    expect($contract['guest_qr_svg'])->toBeNull();
});

test('build omits guest data when guest code is invalid', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'type' => 'addon',
        'slug' => 'guest_book',
    ]);
    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($user, null, $product);
    $orderService->updateOrderStatus($order, 'paid');

    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $contract = $this->builder->build($invitation, null, 'nonexistent-code');

    expect($contract['guest_book_enabled'])->toBeTrue();
    expect($contract['guest'])->toBeNull();
    expect($contract['guest_qr_svg'])->toBeNull();
});

test('buildTemplateDefaults includes demo guest QR and guest book url', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $templatePath = storage_path("app/public/templates/{$slug}");

    File::makeDirectory($templatePath, 0755, true);
    File::put($templatePath.'/template.json', json_encode(['defaults' => []]));

    $contract = $this->builder->buildTemplateDefaults($template);

    expect($contract['guest_qr_demo'])->toBeString()->toContain('<svg');
    expect($contract['guest_book_url'])->toBeString();
});

test('buildTemplateDefaults passes photo credits from template defaults', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $templatePath = storage_path("app/public/templates/{$slug}");

    File::makeDirectory($templatePath, 0755, true);
    File::put($templatePath.'/template.json', json_encode([
        'defaults' => [
            'cover_photo_url' => 'https://example.com/cover.jpg',
            'cover_photo_credit_url' => 'https://id.pinterest.com/pin/937030266222998259/',
            'cover_photo_source_label' => 'Pinterest',
            'gallery' => [
                ['url' => 'https://example.com/one.jpg', 'caption' => null, 'credit_url' => 'https://id.pinterest.com/pin/1/', 'source_label' => 'Pinterest'],
            ],
        ],
    ]));

    $contract = $this->builder->buildTemplateDefaults($template);

    expect($contract['cover_photo_credit_url'])->toBe('https://id.pinterest.com/pin/937030266222998259/');
    expect($contract['cover_photo_source_label'])->toBe('Pinterest');
    expect($contract['gallery'][0]['credit_url'])->toBe('https://id.pinterest.com/pin/1/');
    expect($contract['gallery'][0]['source_label'])->toBe('Pinterest');
});

test('build marks contract as sponsored when user has a template grant', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['slug' => 'sponsored-sunda']);
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['sponsored_by'])->toBeFalse();

    UserGrant::create([
        'user_id' => $user->id,
        'grant_type' => UserGrant::TYPE_TEMPLATE,
        'item_slug' => $template->slug,
    ]);

    $contract = $this->builder->build($invitation->fresh());

    expect($contract['sponsored_by'])->toBeTrue();
});

test('buildTemplateDefaults keeps sponsored_by false', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $templatePath = storage_path("app/public/templates/{$slug}");

    File::makeDirectory($templatePath, 0755, true);
    File::put($templatePath.'/template.json', json_encode(['defaults' => []]));

    $contract = $this->builder->buildTemplateDefaults($template);

    expect($contract['sponsored_by'])->toBeFalse();
});

test('buildTemplateDefaults marks contract as preview', function () {
    $slug = 'test-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);
    $templatePath = storage_path("app/public/templates/{$slug}");

    File::makeDirectory($templatePath, 0755, true);
    File::put($templatePath.'/template.json', json_encode(['defaults' => []]));

    $contract = $this->builder->buildTemplateDefaults($template);

    expect($contract['is_preview'])->toBeTrue();
});

test('build leaves contract unmarked as preview for live invitations', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['is_preview'] ?? null)->toBeNull();
});
