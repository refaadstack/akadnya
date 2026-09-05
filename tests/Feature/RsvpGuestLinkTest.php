<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\Template;
use App\Models\User;

function linkedRsvpInvitation(): Invitation
{
    $user = User::factory()->create();
    $template = Template::factory()->create();

    return Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
        'subdomain' => 'tautan-'.strtolower(str()->random(6)),
    ]);
}

function addGuest(Invitation $invitation, string $name, int $maxPax = 2): Guest
{
    return Guest::create([
        'invitation_id' => $invitation->id,
        'name' => $name,
        'category' => 'friends',
        'max_pax' => $maxPax,
    ]);
}

test('rsvp without a personal link, session, or matching guest is rejected', function () {
    $invitation = linkedRsvpInvitation();

    $response = $this->post("/i/{$invitation->subdomain}/rsvp", [
        'name' => 'Tamu Asing',
        'attendance' => 'yes',
        'pax_count' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('rsvp');
    expect(Rsvp::where('invitation_id', $invitation->id)->count())->toBe(0);
});

test('rsvp binds to the guest from the personal link code', function () {
    $invitation = linkedRsvpInvitation();
    $guest = addGuest($invitation, 'Budi Santoso');

    $response = $this->post("/i/{$invitation->subdomain}/rsvp?guest={$guest->unique_code}", [
        'name' => 'Nama Lain',
        'attendance' => 'yes',
        'pax_count' => 2,
        'message' => 'Selamat!',
    ]);

    $response->assertRedirect()->assertSessionHasNoErrors();

    $rsvp = Rsvp::where('invitation_id', $invitation->id)->first();
    expect($rsvp)->not->toBeNull();
    expect($rsvp->guest_id)->toBe($guest->id);
    expect($rsvp->name)->toBe('Budi Santoso');
});

test('rsvp binds to the guest session from an earlier personal link visit', function () {
    $invitation = linkedRsvpInvitation();
    $guest = addGuest($invitation, 'Siti Aminah');

    $response = $this
        ->withSession(['invitation_guest.'.$invitation->id => $guest->id])
        ->post("/i/{$invitation->subdomain}/rsvp", [
            'name' => 'Siti Aminah',
            'attendance' => 'no',
        ]);

    $response->assertRedirect()->assertSessionHasNoErrors();
    expect(Rsvp::where('invitation_id', $invitation->id)->where('guest_id', $guest->id)->count())->toBe(1);
});

test('resubmitting updates the same confirmation instead of duplicating', function () {
    $invitation = linkedRsvpInvitation();
    $guest = addGuest($invitation, 'Budi Santoso');

    $payload = fn () => $this
        ->withSession(['invitation_guest.'.$invitation->id => $guest->id])
        ->post("/i/{$invitation->subdomain}/rsvp", [
            'name' => 'Budi Santoso',
            'attendance' => 'yes',
            'pax_count' => 1,
            'message' => 'Hadir!',
        ]);

    $payload()->assertRedirect();
    $payload()->assertRedirect();

    expect(Rsvp::where('invitation_id', $invitation->id)->where('guest_id', $guest->id)->count())->toBe(1);
});

test('pax count never exceeds the guest max pax allocation', function () {
    $invitation = linkedRsvpInvitation();
    $guest = addGuest($invitation, 'Budi Santoso', maxPax: 2);

    $this
        ->withSession(['invitation_guest.'.$invitation->id => $guest->id])
        ->post("/i/{$invitation->subdomain}/rsvp", [
            'name' => 'Budi Santoso',
            'attendance' => 'yes',
            'pax_count' => 10,
        ])
        ->assertRedirect();

    expect(Rsvp::where('guest_id', $guest->id)->first()->pax_count)->toBe(2);
});

test('exact single name match links the rsvp as a fallback', function () {
    $invitation = linkedRsvpInvitation();
    $guest = addGuest($invitation, 'Budi Santoso');

    $this
        ->post("/i/{$invitation->subdomain}/rsvp", [
            'name' => 'budi santoso',
            'attendance' => 'yes',
            'pax_count' => 1,
        ])
        ->assertRedirect()->assertSessionHasNoErrors();

    expect(Rsvp::where('guest_id', $guest->id)->count())->toBe(1);
});

test('ambiguous names are rejected instead of guessed', function () {
    $invitation = linkedRsvpInvitation();
    addGuest($invitation, 'Budi Santoso');
    addGuest($invitation, 'Budi Santoso');

    $response = $this->post("/i/{$invitation->subdomain}/rsvp", [
        'name' => 'Budi Santoso',
        'attendance' => 'yes',
        'pax_count' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('rsvp');
    expect(Rsvp::where('invitation_id', $invitation->id)->count())->toBe(0);
});
