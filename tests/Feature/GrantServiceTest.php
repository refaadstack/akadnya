<?php

use App\Models\Invitation;
use App\Models\Order;
use App\Models\Template;
use App\Models\User;
use App\Models\UserGrant;
use App\Services\GrantService;

test('granted user can activate a template for free', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_free' => false, 'price' => 150000]);

    UserGrant::create([
        'user_id' => $user->id,
        'grant_type' => UserGrant::TYPE_TEMPLATE,
        'item_slug' => $template->slug,
    ]);

    $invitation = app(GrantService::class)->activateTemplate($user, $template);

    expect($invitation)->toBeInstanceOf(Invitation::class);
    expect($invitation->user_id)->toBe($user->id);
    expect($user->fresh()->active_invitation_id)->toBe($invitation->id);

    $order = Order::where('user_id', $user->id)->first();
    expect($order)->not->toBeNull();
    expect($order->status)->toBe('paid');
    expect((float) $order->total_amount)->toBe(0.0);
    expect($order->metadata['granted'])->toBeTrue();
});

test('activating an already-owned template returns the existing invitation', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['is_free' => true]);

    UserGrant::create([
        'user_id' => $user->id,
        'grant_type' => UserGrant::TYPE_TEMPLATE,
        'item_slug' => $template->slug,
    ]);

    $first = app(GrantService::class)->activateTemplate($user, $template);
    $second = app(GrantService::class)->activateTemplate($user, $template);

    expect($second->id)->toBe($first->id);
    expect($user->invitations()->count())->toBe(1);
    expect(Order::where('user_id', $user->id)->count())->toBe(1);
});

test('user without grant cannot activate a template', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();

    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    $this->expectExceptionMessage('Anda tidak memiliki akses ke template ini.');

    app(GrantService::class)->activateTemplate($user, $template);
});

test('granted user can activate via the web route', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['slug' => 'sunda-grant-test', 'is_free' => false]);

    UserGrant::create([
        'user_id' => $user->id,
        'grant_type' => UserGrant::TYPE_TEMPLATE,
        'item_slug' => $template->slug,
    ]);

    $this->actingAs($user)
        ->post(route('grants.activate', ['template' => $template->slug]))
        ->assertRedirect(route('dashboard.editor'));

    expect($user->invitations()->where('template_id', $template->id)->exists())->toBeTrue();
});

test('user without grant cannot activate via the web route', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['slug' => 'sunda-denied-test']);

    $this->actingAs($user)
        ->post(route('grants.activate', ['template' => $template->slug]))
        ->assertForbidden();
});

test('templates page flags granted templates for the user', function () {
    $user = User::factory()->create();
    $granted = Template::factory()->create(['slug' => 'granted-template', 'is_free' => false]);
    $regular = Template::factory()->create(['slug' => 'regular-template', 'is_free' => false]);

    UserGrant::create([
        'user_id' => $user->id,
        'grant_type' => UserGrant::TYPE_TEMPLATE,
        'item_slug' => $granted->slug,
    ]);

    $this->actingAs($user)
        ->get(route('templates.index'))
        ->assertInertia(fn ($page) => $page
            ->where('templates.0.is_granted', true)
            ->where('templates.1.is_granted', false)
        );
});
