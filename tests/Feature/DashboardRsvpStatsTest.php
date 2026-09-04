<?php

use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\Template;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

function rsvpStatsInvitation(): Invitation
{
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);
    $user->forceFill(['active_invitation_id' => $invitation->id])->save();

    return $invitation;
}

test('legacy rsvp route redirects to the guests confirmation tab', function () {
    $invitation = rsvpStatsInvitation();

    $this->actingAs($invitation->user)
        ->get('/dashboard/rsvp')
        ->assertRedirectToRoute('dashboard.guests', ['tab' => 'rsvp']);
});

test('guests confirmation tab counts the real attendance values', function () {
    $invitation = rsvpStatsInvitation();

    Rsvp::create(['invitation_id' => $invitation->id, 'name' => 'Hadir A', 'attendance' => 'yes', 'pax_count' => 2]);
    Rsvp::create(['invitation_id' => $invitation->id, 'name' => 'Hadir B', 'attendance' => 'yes', 'pax_count' => 1]);
    Rsvp::create(['invitation_id' => $invitation->id, 'name' => 'Mager', 'attendance' => 'no', 'pax_count' => 0]);

    $response = $this->actingAs($invitation->user)->get('/dashboard/guests?tab=rsvp');

    $response->assertOk()->assertInertia(
        fn (AssertableInertia $page) => $page
            ->where('activeTab', 'rsvp')
            ->where('rsvpStats.total', 3)
            ->where('rsvpStats.hadir', 2)
            ->where('rsvpStats.tidak_hadir', 1)
    );
});
