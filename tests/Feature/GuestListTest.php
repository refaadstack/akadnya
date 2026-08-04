<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Services\OrderService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $template = Template::factory()->create();
    $basePackage = Product::factory()->create([
        'type' => 'base_package',
        'slug' => 'base',
        'price' => 50000,
    ]);

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($this->user, $template, $basePackage, []);
    $orderService->updateOrderStatus($order, 'paid');

    $this->invitation = Invitation::where('user_id', $this->user->id)->firstOrFail();
    $this->user->forceFill(['active_invitation_id' => $this->invitation->id])->save();
});

test('guest list exposes invitation status', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard.guests'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('invitation.id', $this->invitation->id)
            ->where('invitation.status', $this->invitation->status));
});

test('guest personal link includes guest code and name', function () {
    $guest = Guest::create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Budi Santoso',
        'category' => 'family',
    ]);

    $this->actingAs($this->user)
        ->get(route('dashboard.guests'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('guests.data.0.personal_link', $guest->getPersonalLink())
            ->where('guests.data.0.personal_link', fn ($link) =>
                str_contains($link, 'guest='.$guest->unique_code)
                && str_contains($link, 'name=Budi+Santoso')));
});
