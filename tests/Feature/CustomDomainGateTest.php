<?php

use App\Models\Invitation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Template;
use App\Models\User;
use App\Models\UserFeature;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

function customDomainFeatureForUser(User $user): UserFeature
{
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.uniqid(),
        'status' => 'paid',
        'total_amount' => 1000,
    ]);
    $item = OrderItem::create([
        'order_id' => $order->id,
        'item_type' => 'product',
        'item_id' => 1,
        'name' => 'Custom Domain',
        'price' => 1000,
    ]);

    return UserFeature::create([
        'user_id' => $user->id,
        'feature' => 'custom_domain',
        'order_item_id' => $item->id,
        'activated_at' => now(),
    ]);
}

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $this->user = User::factory()->create();
    $this->template = Template::factory()->create();
    $this->invitation = Invitation::factory()->for($this->user)->for($this->template)->create();
    $this->user->forceFill(['active_invitation_id' => $this->invitation->id])->save();
});

test('setting a custom domain requires the custom_domain add-on', function () {
    $this->actingAs($this->user)
        ->post(route('dashboard.settings.custom-domain'), [
            'custom_domain' => 'undangan.example.com',
        ])
        ->assertForbidden();

    expect($this->invitation->fresh()->custom_domain)->toBeNull();
});

test('custom domain is saved when the user owns the add-on', function () {
    customDomainFeatureForUser($this->user);

    $this->actingAs($this->user)
        ->post(route('dashboard.settings.custom-domain'), [
            'custom_domain' => 'undangan.example.com',
        ])
        ->assertRedirect();

    expect($this->invitation->fresh()->custom_domain)->toBe('undangan.example.com');
});

test('clearing a custom domain stays available without the add-on', function () {
    $this->invitation->update(['custom_domain' => 'undangan.example.com']);

    $this->actingAs($this->user)
        ->post(route('dashboard.settings.custom-domain'), [
            'custom_domain' => null,
        ])
        ->assertRedirect();

    expect($this->invitation->fresh()->custom_domain)->toBeNull();
});

test('settings page exposes has_custom_domain flag', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard.settings'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('has_custom_domain')
        );
});