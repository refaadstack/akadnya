<?php

use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\Template;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('dashboard rsvp stats count the real attendance values', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);

    Rsvp::create(['invitation_id' => $invitation->id, 'name' => 'Hadir A', 'attendance' => 'yes', 'pax_count' => 2]);
    Rsvp::create(['invitation_id' => $invitation->id, 'name' => 'Hadir B', 'attendance' => 'yes', 'pax_count' => 1]);
    Rsvp::create(['invitation_id' => $invitation->id, 'name' => 'Mager', 'attendance' => 'no', 'pax_count' => 0]);

    $response = $this->actingAs($user)->get('/dashboard/rsvp');

    $response->assertOk()->assertInertia(
        fn (AssertableInertia $page) => $page
            ->where('stats.total', 3)
            ->where('stats.hadir', 2)
            ->where('stats.tidak_hadir', 1)
    );
});
