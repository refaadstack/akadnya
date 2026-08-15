<?php

use App\Filament\Pages\ManageFees;
use App\Models\SiteSetting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    SiteSetting::flush();
});

test('admin can open the fees page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/manage-fees')
        ->assertOk();
});

test('non-admin cannot open the fees page', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get('/admin/manage-fees')
        ->assertForbidden();
});

test('saving the form persists the fee settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(ManageFees::class)
        ->set('data.tax_enabled', true)
        ->set('data.tax_rate', 12)
        ->set('data.payment_gateway_fee_percentage', 3)
        ->set('data.payment_gateway_fee_flat', 5000)
        ->set('data.payment_gateway_fee_default_rule', 'flat')
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteSetting::get('tax_enabled'))->toBe('1')
        ->and(SiteSetting::get('tax_rate'))->toBe('12')
        ->and(SiteSetting::get('payment_gateway_fee_percentage'))->toBe('3')
        ->and(SiteSetting::get('payment_gateway_fee_flat'))->toBe('5000')
        ->and(SiteSetting::get('payment_gateway_fee_default_rule'))->toBe('flat');
});

test('form is prefilled with the stored settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    SiteSetting::set('tax_enabled', true);
    SiteSetting::set('tax_rate', 12);
    SiteSetting::set('payment_gateway_fee_percentage', 3);
    SiteSetting::set('payment_gateway_fee_flat', 5000);
    SiteSetting::set('payment_gateway_fee_default_rule', 'flat');

    Livewire::actingAs($admin)
        ->test(ManageFees::class)
        ->assertFormSet([
            'tax_enabled' => true,
            'tax_rate' => 12,
            'payment_gateway_fee_percentage' => 3,
            'payment_gateway_fee_flat' => 5000,
            'payment_gateway_fee_default_rule' => 'flat',
        ]);
});

test('form is prefilled with config defaults when nothing is stored', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(ManageFees::class)
        ->assertFormSet([
            'tax_enabled' => false,
            'tax_rate' => 11,
            'payment_gateway_fee_percentage' => 2,
            'payment_gateway_fee_flat' => 4000,
            'payment_gateway_fee_default_rule' => 'percentage',
        ]);
});