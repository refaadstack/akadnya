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

test('dashboard analytics include attendance rate, check-ins and rsvp trend', function () {
    $user = User::factory()->create();
    $invitation = Invitation::factory()->for($user)->create();

    $user->forceFill(['active_invitation_id' => $invitation->id])->save();
    $invitation->rsvps()->create([
        'name' => 'Budi',
        'attendance' => 'yes',
        'pax_count' => 2,
    ]);
    $invitation->rsvps()->create([
        'name' => 'Sari',
        'attendance' => 'no',
        'pax_count' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('analytics.rsvp_trend', 14)
            ->where('analytics.attendance_rate', 50)
            ->where('analytics.total_check_ins', 0)
            ->where('analytics.rsvp_trend.13.total', 2)
            ->where('analytics.rsvp_trend.13.attending', 1)
        );
});

test('dashboard exposes all active templates with ownership flags', function () {
    $user = User::factory()->create();
    $owned = Template::factory()->create(['name' => 'Owned Template']);
    $other = Template::factory()->create(['name' => 'Another Template']);
    Template::factory()->inactive()->create(['name' => 'Hidden Template']);

    Invitation::factory()->for($user)->for($owned)->create([
        'subdomain' => 'owned-'.strtolower(str()->random(6)),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('allTemplates.0.name', 'Another Template')
            ->where('allTemplates.0.is_owned', false)
            ->where('allTemplates.1.name', 'Owned Template')
            ->where('allTemplates.1.is_owned', true)
            ->has('allTemplates', 2)
        );
});

test('user cannot adopt a template they do not own', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();

    $this->actingAs($user)
        ->post(route('dashboard.templates.select', $template))
        ->assertRedirect(route('templates.show', ['slug' => $template->slug]));

    expect(Invitation::where('template_id', $template->id)->exists())->toBeFalse();
    expect($user->fresh()->active_invitation_id)->toBeNull();
});

test('user can adopt a template they own via a paid order', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();

    $order = \App\Models\Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-TEST-'.strtoupper(str()->random(6)),
        'status' => 'paid',
        'total_amount' => $template->price,
        'subtotal_amount' => $template->price,
        'payment_gateway_fee' => 0,
        'tax_amount' => 0,
        'paid_at' => now(),
    ]);
    $order->items()->create([
        'item_type' => 'template',
        'item_id' => $template->id,
        'name' => $template->name,
        'price' => $template->price,
    ]);

    $this->actingAs($user)
        ->post(route('dashboard.templates.select', $template))
        ->assertRedirect(route('dashboard.editor'));

    $invitation = Invitation::where('template_id', $template->id)->first();
    expect($invitation)->not->toBeNull()
        ->and($invitation->user_id)->toBe($user->id)
        ->and($user->fresh()->active_invitation_id)->toBe($invitation->id);
});

test('selecting an inactive template is forbidden', function () {
    $user = User::factory()->create();
    $template = Template::factory()->inactive()->create();

    $this->actingAs($user)
        ->post(route('dashboard.templates.select', $template))
        ->assertNotFound();
});

test('selecting an already-owned template just switches to it without duplicating', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $order = \App\Models\Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-TEST-'.strtoupper(str()->random(6)),
        'status' => 'paid',
        'total_amount' => $template->price,
        'subtotal_amount' => $template->price,
        'payment_gateway_fee' => 0,
        'tax_amount' => 0,
        'paid_at' => now(),
    ]);
    $order->items()->create([
        'item_type' => 'template',
        'item_id' => $template->id,
        'name' => $template->name,
        'price' => $template->price,
    ]);
    $invitation = Invitation::factory()->for($user)->for($template)->create([
        'subdomain' => 'already-'.strtolower(str()->random(6)),
    ]);

    $this->actingAs($user)
        ->post(route('dashboard.templates.select', $template))
        ->assertRedirect(route('dashboard.editor'));

    expect(Invitation::where('template_id', $template->id)->count())->toBe(1)
        ->and($user->fresh()->active_invitation_id)->toBe($invitation->id);
});
