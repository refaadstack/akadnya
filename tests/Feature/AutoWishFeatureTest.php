<?php

use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use App\Services\DataContractBuilder;

function createdAutoWishInvitation(): Invitation
{
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->for($user)->for($template)->create([
        'subdomain' => 'auto-wish-'.strtolower(str()->random(6)),
    ]);

    $invitation->content()->create([
        'bride_name' => 'Nadia',
        'groom_name' => 'Raka',
        'akad_datetime' => '2026-06-14 09:00:00',
        'akad_venue' => 'Gedung Merdeka',
        'show_wishes' => true,
    ]);

    $user->forceFill(['active_invitation_id' => $invitation->id])->save();

    return $invitation;
}

test('publish seeds a personalized welcome wish from Akadnya', function () {
    $invitation = createdAutoWishInvitation();

    $this->actingAs($invitation->user)->post('/dashboard/publish');
    $invitation->refresh();

    expect($invitation->status)->toBe('published');

    $wish = $invitation->rsvps()->where('is_from_akadnya', true)->first();

    expect($wish)->not->toBeNull()
        ->and($wish->name)->toBe('Akadnya')
        ->and($wish->attendance)->toBe('pending')
        ->and($wish->pax_count)->toBe(0)
        ->and($wish->message)->toContain('Raka & Nadia')
        ->and($wish->message)->toContain('sakinah, mawaddah, warahmah');
});

test('republishing does not duplicate the Akadnya wish', function () {
    $invitation = createdAutoWishInvitation();

    $this->actingAs($invitation->user)->post('/dashboard/publish');
    $this->actingAs($invitation->user)->post('/dashboard/publish');
    $this->actingAs($invitation->user)->post('/dashboard/publish');

    expect($invitation->rsvps()->where('is_from_akadnya', true)->count())->toBe(1);
});

test('Akadnya wish appears in the public data contract', function () {
    $invitation = createdAutoWishInvitation();

    $this->actingAs($invitation->user)->post('/dashboard/publish');

    $contract = app(DataContractBuilder::class)->build($invitation->fresh());

    $names = collect($contract['wishes'])->pluck('name');
    expect($names)->toContain('Akadnya');

    $wish = collect($contract['wishes'])->firstWhere('name', 'Akadnya');
    expect($wish['message'])->toContain('Raka & Nadia');
});

test('the Akadnya wish is served by the public wishes endpoint', function () {
    $invitation = createdAutoWishInvitation();

    $this->actingAs($invitation->user)->post('/dashboard/publish');

    $response = $this->getJson("/i/{$invitation->subdomain}/wishes");

    $response->assertOk();
    expect(collect($response->json('data'))->where('name', 'Akadnya'))->not->toBeEmpty();
});

test('auto wish is not created when wishes are hidden', function () {
    $invitation = createdAutoWishInvitation();
    $invitation->content()->update(['show_wishes' => false]);

    $this->actingAs($invitation->user)->post('/dashboard/publish');

    expect($invitation->rsvps()->where('is_from_akadnya', true)->count())->toBe(0);
});

test('auto wish does not pollute rsvp analytics and roster', function () {
    $invitation = createdAutoWishInvitation();

    $this->actingAs($invitation->user)->post('/dashboard/publish');

    $this->actingAs($invitation->user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('analytics.total_rsvp', 0)
            ->where('analytics.total_wishes', 1)
            ->where('analytics.rsvp_trend.13.total', 0)
        );

    $this->actingAs($invitation->user)
        ->get('/dashboard/rsvp')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('stats.total', 0));
});

test('backfill seeds the Akadnya wish for published invitations that lack one', function () {
    $published = createdAutoWishInvitation();
    $published->forceFill(['status' => 'published', 'published_at' => now()])->save();

    $draft = createdAutoWishInvitation();

    $this->artisan('invitations:backfill-auto-wishes')->assertSuccessful();

    expect($published->rsvps()->where('is_from_akadnya', true)->count())->toBe(1);
    expect($draft->rsvps()->where('is_from_akadnya', true)->count())->toBe(0);
});

test('backfill is idempotent and never duplicates the Akadnya wish', function () {
    $published = createdAutoWishInvitation();
    $published->forceFill(['status' => 'published', 'published_at' => now()])->save();

    $this->artisan('invitations:backfill-auto-wishes')->assertSuccessful();
    $this->artisan('invitations:backfill-auto-wishes')->assertSuccessful();

    expect($published->rsvps()->where('is_from_akadnya', true)->count())->toBe(1);
});
