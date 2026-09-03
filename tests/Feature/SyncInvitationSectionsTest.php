<?php

use App\Models\Invitation;
use App\Models\Template;
use App\Models\TemplateSection;
use App\Models\User;

function invitationWithoutSections(): Invitation
{
    $user = User::factory()->create();
    $template = Template::factory()->create();

    TemplateSection::create([
        'template_id' => $template->id,
        'file' => 'hero.html',
        'label' => 'Hero Section',
        'sort_order' => 1,
        'is_required' => true,
    ]);
    TemplateSection::create([
        'template_id' => $template->id,
        'file' => 'gallery.html',
        'label' => 'Photo Gallery',
        'sort_order' => 2,
        'is_required' => false,
    ]);

    $invitation = Invitation::factory()->for($user)->for($template)->create([
        'subdomain' => 'sync-sections-'.strtolower(str()->random(6)),
    ]);

    $user->forceFill(['active_invitation_id' => $invitation->id])->save();

    return $invitation;
}

test('sync-sections backfills missing sections for invitations', function () {
    $invitation = invitationWithoutSections();

    expect($invitation->sections()->count())->toBe(0);

    $this->artisan('invitations:sync-sections')->assertSuccessful();

    expect($invitation->sections()->count())->toBe(2);

    $first = $invitation->sections()->orderBy('sort_order')->first();
    expect($first->templateSection->file)->toBe('hero.html')
        ->and($first->is_visible)->toBeTrue();
});

test('sync-sections is idempotent and never duplicates', function () {
    $invitation = invitationWithoutSections();

    $this->artisan('invitations:sync-sections')->assertSuccessful();
    $this->artisan('invitations:sync-sections')->assertSuccessful();

    expect($invitation->sections()->count())->toBe(2);
});

test('sync-sections is a no-op when every invitation already has sections', function () {
    $invitation = invitationWithoutSections();

    $this->artisan('invitations:sync-sections')->assertSuccessful();

    expect($invitation->sections()->count())->toBe(2);

    $this->artisan('invitations:sync-sections')->assertSuccessful();
    expect($invitation->sections()->count())->toBe(2);
});
