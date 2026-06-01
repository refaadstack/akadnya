<?php

use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows owned invitation template options and active invitation', function () {
    $user = User::factory()->create();
    $firstTemplate = Template::factory()->create(['name' => 'Betawi Heritage']);
    $secondTemplate = Template::factory()->create(['name' => 'Palembang Classic']);
    $firstInvitation = Invitation::factory()->for($user)->for($firstTemplate)->create();
    $secondInvitation = Invitation::factory()->for($user)->for($secondTemplate)->create();

    $user->forceFill(['active_invitation_id' => $secondInvitation->id])->save();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('invitationOptions', 2)
            ->where('invitation.id', $secondInvitation->id)
            ->where('invitationOptions.0.is_active', true)
        );
});

test('user can select active invitation from owned templates', function () {
    $user = User::factory()->create();
    $firstInvitation = Invitation::factory()->for($user)->create();
    $secondInvitation = Invitation::factory()->for($user)->create();

    $user->forceFill(['active_invitation_id' => $firstInvitation->id])->save();

    $this->actingAs($user)
        ->post(route('dashboard.invitations.select', $secondInvitation))
        ->assertRedirect();

    expect($user->fresh()->active_invitation_id)->toBe($secondInvitation->id);
});
