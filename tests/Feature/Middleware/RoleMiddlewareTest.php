<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can access admin routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Create a test route with role middleware
    Route::get('/test-admin', fn () => 'admin content')->middleware(['auth', 'role:admin']);

    $response = $this->actingAs($admin)->get('/test-admin');

    $response->assertSuccessful();
    $response->assertSee('admin content');
});

test('regular user cannot access admin routes', function () {
    $user = User::factory()->create(['role' => 'user']);

    // Create a test route with role middleware
    Route::get('/test-admin', fn () => 'admin content')->middleware(['auth', 'role:admin']);

    $response = $this->actingAs($user)->get('/test-admin');

    $response->assertForbidden();
});

test('guest cannot access admin routes', function () {
    // Create a test route with role middleware
    Route::get('/test-admin', fn () => 'admin content')->middleware(['auth', 'role:admin']);

    $response = $this->get('/test-admin');

    $response->assertRedirect('/login');
});

test('user can access user routes', function () {
    $user = User::factory()->create(['role' => 'user']);

    // Create a test route with role middleware
    Route::get('/test-user', fn () => 'user content')->middleware(['auth', 'role:user']);

    $response = $this->actingAs($user)->get('/test-user');

    $response->assertSuccessful();
    $response->assertSee('user content');
});
