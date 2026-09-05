<?php

use App\Models\Invitation;
use App\Models\InvitationGallery;
use App\Models\Template;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('gallery page exposes uploaded photos with host-relative urls', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);
    $user->forceFill(['active_invitation_id' => $invitation->id])->save();

    InvitationGallery::create([
        'invitation_id' => $invitation->id,
        'image_url' => 'https://akadnya.com/storage/invitations/gallery/foto.jpeg',
        'caption' => 'Foto',
        'sort_order' => 1,
    ]);

    $this->actingAs($user)
        ->get('/dashboard/gallery')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('gallery', 1)
            ->where('gallery.0.image_url', '/storage/invitations/gallery/foto.jpeg')
            ->where('gallery.0.caption', 'Foto')
        );
});
