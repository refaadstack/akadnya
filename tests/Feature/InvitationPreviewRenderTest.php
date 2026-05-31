<?php

use App\Http\Middleware\HasBasePackage;
use App\Models\Invitation;
use App\Models\InvitationContent;
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
