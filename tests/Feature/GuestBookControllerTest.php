<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Services\GuestBookService;
use App\Services\OrderService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

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

    $guestBookProduct = Product::factory()->create([
        'type' => 'addon',
        'slug' => 'guest_book',
    ]);
    $guestBookOrder = $orderService->createOrder($this->user, null, $guestBookProduct);
    $orderService->updateOrderStatus($guestBookOrder, 'paid');

    $this->guest = Guest::create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Budi Santoso',
        'category' => 'family',
    ]);
});

test('guest book index requires the guest_book feature', function () {
    $this->user->features()->where('feature', 'guest_book')->delete();

    $this->actingAs($this->user)
        ->get(route('dashboard.guest-book'))
        ->assertForbidden();
});

test('guest book index shows stats and entries', function () {
    $service = app(GuestBookService::class);
    $service->checkIn($this->invitation, $this->guest);
    $service->takeSouvenir($this->invitation, $this->guest);

    $this->actingAs($this->user)
        ->get(route('dashboard.guest-book'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.checked_in', 1)
            ->where('stats.souvenirs', 1)
            ->where('stats.raffles', 0)
            ->has('entries', 2)
            ->where('entries', fn ($entries) =>
                collect($entries)->pluck('event_type')->sort()->values()->all() === ['check_in', 'souvenir'])
            ->where('entries.0.guest_id', $this->guest->id)
            ->where('entries.0.guest_name', 'Budi Santoso'));
});

test('check-in by guest code registers an entry', function () {
    $this->actingAs($this->user)
        ->post(route('dashboard.guest-book.check-in'), [
            'code' => $this->guest->unique_code,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->invitation->guestBookEntries()->where('event_type', 'check_in')->count())->toBe(1);
});

test('duplicate check-in is rejected', function () {
    $service = app(GuestBookService::class);
    $service->checkIn($this->invitation, $this->guest);

    $this->actingAs($this->user)
        ->post(route('dashboard.guest-book.check-in'), [
            'code' => $this->guest->unique_code,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('souvenir requires a prior check-in', function () {
    $this->actingAs($this->user)
        ->post(route('dashboard.guest-book.souvenir'), [
            'guest_id' => $this->guest->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('raffle draws a winner among checked-in guests', function () {
    $service = app(GuestBookService::class);
    $service->checkIn($this->invitation, $this->guest);

    $this->actingAs($this->user)
        ->post(route('dashboard.guest-book.raffle'))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->invitation->guestBookEntries()->where('event_type', 'raffle')->count())->toBe(1);
});

test('raffle fails when nobody is checked in', function () {
    $this->actingAs($this->user)
        ->post(route('dashboard.guest-book.raffle'))
        ->assertRedirect()
        ->assertSessionHas('error');
});
