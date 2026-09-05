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

test('gallery reorder endpoint is reachable and persists the new order', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
    ]);
    $user->forceFill(['active_invitation_id' => $invitation->id])->save();

    $first = InvitationGallery::create([
        'invitation_id' => $invitation->id,
        'image_url' => 'https://akadnya.com/storage/invitations/gallery/a.jpeg',
        'sort_order' => 0,
    ]);
    $second = InvitationGallery::create([
        'invitation_id' => $invitation->id,
        'image_url' => 'https://akadnya.com/storage/invitations/gallery/b.jpeg',
        'sort_order' => 1,
    ]);

    $this->actingAs($user)
        ->post('/dashboard/gallery/reorder', [
            'photos' => [
                ['id' => $second->id, 'sort_order' => 0],
                ['id' => $first->id, 'sort_order' => 1],
            ],
        ])
        ->assertRedirect();

    expect($first->fresh()->sort_order)->toBe(1);
    expect($second->fresh()->sort_order)->toBe(0);
});
