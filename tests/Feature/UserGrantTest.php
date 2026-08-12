<?php

use App\Models\Template;
use App\Models\User;
use App\Models\UserGrant;

test('hasGrant matches a specific addon slug', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);

    UserGrant::create([
        'user_id' => $user->id,
        'granted_by' => $admin->id,
        'grant_type' => UserGrant::TYPE_ADDON,
        'item_slug' => 'guest_book',
    ]);

    expect($user->hasGrant(UserGrant::TYPE_ADDON, 'guest_book'))->toBeTrue();
    expect($user->hasGrant(UserGrant::TYPE_ADDON, 'custom_domain'))->toBeFalse();
});

test('hasGrant with null slug covers every item of that type', function () {
    $user = User::factory()->create();

    UserGrant::create([
        'user_id' => $user->id,
        'grant_type' => UserGrant::TYPE_TEMPLATE,
        'item_slug' => null,
    ]);

    expect($user->hasGrant(UserGrant::TYPE_TEMPLATE, 'any-template'))->toBeTrue();
});

test('expired grant does not apply', function () {
    $user = User::factory()->create();

    UserGrant::create([
        'user_id' => $user->id,
        'grant_type' => UserGrant::TYPE_ADDON,
        'item_slug' => 'guest_book',
        'expires_at' => now()->subDay(),
    ]);

    expect($user->hasGrant(UserGrant::TYPE_ADDON, 'guest_book'))->toBeFalse();
});

test('hasFeature is satisfied by an addon grant', function () {
    $user = User::factory()->create();

    UserGrant::create([
        'user_id' => $user->id,
        'grant_type' => UserGrant::TYPE_ADDON,
        'item_slug' => 'guest_book',
    ]);

    expect($user->hasFeature('guest_book'))->toBeTrue();
    expect($user->hasFeature('custom_domain'))->toBeFalse();
});

test('hasTemplateAccess matches template slug grant', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create(['slug' => 'sunda-merbak']);
    $other = Template::factory()->create(['slug' => 'noir-luxe']);

    UserGrant::create([
        'user_id' => $user->id,
        'grant_type' => UserGrant::TYPE_TEMPLATE,
        'item_slug' => 'sunda-merbak',
    ]);

    expect($user->hasTemplateAccess($template))->toBeTrue();
    expect($user->hasTemplateAccess($other))->toBeFalse();
});

test('isPrivileged is true when the user has any active grant', function () {
    $user = User::factory()->create();

    expect($user->isPrivileged())->toBeFalse();

    UserGrant::create([
        'user_id' => $user->id,
        'grant_type' => UserGrant::TYPE_ADDON,
        'item_slug' => 'guest_book',
    ]);

    expect($user->isPrivileged())->toBeTrue();
});
