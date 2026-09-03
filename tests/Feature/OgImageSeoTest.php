<?php

use App\Models\Invitation;
use App\Models\InvitationContent;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

afterEach(function () {
    $templatesPath = storage_path('app/public/templates');

    if (is_dir($templatesPath)) {
        foreach (glob($templatesPath.'/test-*') as $dir) {
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }
    }

    foreach (glob(storage_path('app/public/invitations/covers/test-*')) as $file) {
        if (is_file($file)) {
            File::delete($file);
        }
    }

    foreach (glob(storage_path('app/public/og/invitations/*')) as $file) {
        if (is_file($file)) {
            File::delete($file);
        }
    }
});

function ogTestTemplate(string $slug): Template
{
    $template = Template::factory()->create(['slug' => $slug]);
    $template->sections()->create([
        'file' => 'hero.html',
        'label' => 'Hero',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $templatePath = storage_path("app/public/templates/{$slug}");
    File::makeDirectory($templatePath.'/sections', 0755, true);
    File::put($templatePath.'/sections/hero.html', '<section class="og-hero">hero</section>');

    return $template;
}

test('public invitation renders a landscape og:image generated from a portrait cover', function () {
    if (! extension_loaded('gd')) {
        test()->skip('GD extension is required to render the OG image');
    }

    $user = User::factory()->create();
    $slug = 'test-'.uniqid();
    $template = ogTestTemplate($slug);

    $coverPath = storage_path('app/public/invitations/covers/test-'.uniqid().'.jpg');
    File::ensureDirectoryExists(dirname($coverPath));

    $src = imagecreatetruecolor(3120, 4160);
    $red = imagecolorallocate($src, 200, 60, 60);
    imagefill($src, 0, 0, $red);
    imagejpeg($src, $coverPath, 90);
    imagedestroy($src);

    $coverUrl = rtrim(config('app.url'), '/').'/storage/'.Str::after($coverPath, storage_path('app/public').'/');

    $invitation = Invitation::factory()->published()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Nadia',
        'groom_name' => 'Raka',
        'cover_photo_url' => $coverUrl,
    ]);
    $invitation->sections()->create([
        'template_section_id' => $template->sections()->first()->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $response = $this->get('/i/'.$invitation->subdomain);
    $response->assertOk();

    $appUrl = rtrim(config('app.url'), '/');
    $response->assertSee('<meta property="og:image" content="'.$appUrl.'/storage/og/invitations/', false);
    $response->assertSee('<meta property="og:image:width" content="1200">', false);
    $response->assertSee('<meta property="og:image:height" content="630">', false);
    $response->assertSee('<meta property="og:image:type" content="image/jpeg">', false);
    $response->assertDontSee('favicon', false);
});

test('invitation without a cover photo falls back to an akadnya.com branded placeholder, never localhost', function () {
    $user = User::factory()->create();
    $slug = 'test-'.uniqid();
    $template = ogTestTemplate($slug);

    $invitation = Invitation::factory()->published()->for($user)->for($template)->create();
    InvitationContent::create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Budi',
        'groom_name' => 'Sari',
        'cover_photo_url' => null,
        'couple_photo_url' => null,
        'bride_photo_url' => null,
        'groom_photo_url' => null,
    ]);
    $invitation->sections()->create([
        'template_section_id' => $template->sections()->first()->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $response = $this->get('/i/'.$invitation->subdomain);
    $response->assertOk();

    $appUrl = rtrim(config('app.url'), '/');
    $response->assertSee('<meta property="og:image" content="'.$appUrl.'/images/placeholder-template.png"', false);
    $response->assertDontSee('favicon.svg', false);
});
