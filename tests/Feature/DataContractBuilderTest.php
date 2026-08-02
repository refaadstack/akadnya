<?php

use App\Models\Invitation;
use App\Models\InvitationContent;
use App\Models\InvitationGallery;
use App\Models\Template;
use App\Models\User;
use App\Services\DataContractBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->builder = new DataContractBuilder;
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
        'bride_father',
        'bride_mother',
        'bride_photo_url',
        'groom_name',
        'groom_father',
        'groom_mother',
        'groom_photo_url',
        'couple_photo_url',
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
    ]);

    $contract = $this->builder->build($invitation);

    expect($contract['bride_name'])->toBe('Siti');
    expect($contract['groom_name'])->toBe('Budi');
    expect($contract['akad_venue'])->toBe('Masjid Al-Ikhlas');
    expect($contract['akad_datetime_formatted'])->toBeString();
    expect($contract['akad_time'])->toContain('WIB');
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

test('build includes couple photo, music title and gift address', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
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
