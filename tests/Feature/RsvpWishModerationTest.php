<?php

use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\Template;
use App\Models\User;
use App\Services\DataContractBuilder;

function invitationWithWishes(): Invitation
{
    $user = User::factory()->create();
    $template = Template::factory()->create();

    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
        'subdomain' => 'moderasi-'.strtolower(str()->random(6)),
    ]);

    Rsvp::create([
        'invitation_id' => $invitation->id,
        'name' => 'Tamu Baik',
        'attendance' => 'yes',
        'pax_count' => 1,
        'message' => 'Selamat menempati hidup baru!',
    ]);

    Rsvp::create([
        'invitation_id' => $invitation->id,
        'name' => 'Tamu Judi',
        'attendance' => 'yes',
        'pax_count' => 2,
        'message' => 'promosi judi online disini',
    ]);

    return $invitation;
}

test('owner can hide a wish from the public invitation', function () {
    $invitation = invitationWithWishes();
    $rsvp = $invitation->rsvps()->where('name', 'Tamu Judi')->first();

    $response = $this->actingAs($invitation->user)->post("/dashboard/rsvp/{$rsvp->id}/hide");

    $response->assertRedirect();
    expect($rsvp->fresh()->is_hidden)->toBeTrue();
});

test('owner can show a hidden wish again', function () {
    $invitation = invitationWithWishes();
    $rsvp = $invitation->rsvps()->where('name', 'Tamu Judi')->first();
    $rsvp->update(['is_hidden' => true]);

    $response = $this->actingAs($invitation->user)->post("/dashboard/rsvp/{$rsvp->id}/show");

    $response->assertRedirect();
    expect($rsvp->fresh()->is_hidden)->toBeFalse();
});

test('guest cannot hide wishes on someone elses invitation', function () {
    $invitation = invitationWithWishes();
    $rsvp = $invitation->rsvps()->first();

    $stranger = User::factory()->create();
    $strangerTemplate = Template::factory()->create();
    Invitation::factory()->create([
        'user_id' => $stranger->id,
        'template_id' => $strangerTemplate->id,
    ]);

    $this->actingAs($stranger)->post("/dashboard/rsvp/{$rsvp->id}/hide")->assertForbidden();
    $this->actingAs($stranger)->post("/dashboard/rsvp/{$rsvp->id}/show")->assertForbidden();

    expect($rsvp->fresh()->is_hidden)->toBeFalse();
});

test('data contract excludes hidden wishes', function () {
    $invitation = invitationWithWishes();
    $invitation->rsvps()->where('name', 'Tamu Judi')->update(['is_hidden' => true]);

    $contract = app(DataContractBuilder::class)->build($invitation);

    $names = collect($contract['wishes'])->pluck('name');
    expect($names)->toContain('Tamu Baik');
    expect($names)->not->toContain('Tamu Judi');
});

test('public wishes endpoint excludes hidden wishes', function () {
    $invitation = invitationWithWishes();
    $invitation->rsvps()->where('name', 'Tamu Judi')->update(['is_hidden' => true]);

    $response = $this->getJson("/i/{$invitation->subdomain}/wishes");

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name');
    expect($names)->toContain('Tamu Baik');
    expect($names)->not->toContain('Tamu Judi');
});
