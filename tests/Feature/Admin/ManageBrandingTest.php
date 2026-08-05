<?php

use App\Filament\Pages\ManageBranding;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    SiteSetting::flush();
});

test('admin can open the branding page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/manage-branding')
        ->assertOk();
});

test('non-admin cannot open the branding page', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get('/admin/manage-branding')
        ->assertForbidden();
});

test('saving a logo stores its public url', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Storage::disk('public')->put('branding/test-logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

    Livewire::actingAs($admin)
        ->test(ManageBranding::class)
        ->set('data.qr_logo', ['branding/test-logo.svg'])
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteSetting::get('qr_logo_url'))->toContain('/storage/branding/test-logo.svg');
});

test('clearing the logo falls back to the default', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    SiteSetting::set('qr_logo_url', 'https://example.com/logo.svg');

    Livewire::actingAs($admin)
        ->test(ManageBranding::class)
        ->set('data.qr_logo', null)
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteSetting::get('qr_logo_url'))->toBeNull();
});
