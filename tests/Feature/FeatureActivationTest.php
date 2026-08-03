<?php

use App\Models\Invitation;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Models\UserFeature;
use App\Services\OrderService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->template = Template::factory()->create(['price' => 100000]);
});

test('product features are activated after payment', function () {
    $product = Product::factory()->create([
        'type' => 'base_package',
        'slug' => 'base',
        'price' => 50000,
    ]);

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($this->user, null, $product);
    $orderService->updateOrderStatus($order, 'paid');

    expect(UserFeature::where('user_id', $this->user->id)->count())->toBe(1);

    $feature = UserFeature::where('user_id', $this->user->id)
        ->where('feature', 'base')
        ->first();
    expect($feature)->not->toBeNull()
        ->and($feature->isActive())->toBeTrue()
        ->and($feature->expires_at)->toBeNull(); // Base package doesn't expire
});

test('recurring product feature expires per interval', function () {
    $product = Product::factory()->create([
        'type' => 'addon',
        'slug' => 'premium-music',
        'is_recurring' => true,
        'recurring_interval' => 'monthly',
    ]);

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($this->user, null, $product);
    $orderService->updateOrderStatus($order, 'paid');

    $feature = UserFeature::where('user_id', $this->user->id)
        ->where('feature', 'premium-music')
        ->first();

    expect($feature->expires_at)->not->toBeNull();

    $expectedExpiry = now()->addMonth();
    expect($feature->expires_at->diffInDays($expectedExpiry))->toBeLessThan(1);
});

test('template purchase alone creates invitation and grants access', function () {
    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($this->user, $this->template, null);
    $orderService->updateOrderStatus($order, 'paid');

    $invitation = Invitation::where('user_id', $this->user->id)->first();
    expect($invitation)->not->toBeNull()
        ->and($invitation->template_id)->toBe($this->template->id)
        ->and($invitation->status)->toBe('draft')
        ->and($this->user->invitations()->count())->toBe(1);
});

test('template purchase without preview data still creates invitation', function () {
    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($this->user, $this->template, null);
    $orderService->updateOrderStatus($order, 'paid');

    $invitation = Invitation::where('user_id', $this->user->id)->first();
    expect($invitation)->not->toBeNull();

    $content = $invitation->content;
    expect($content)->not->toBeNull()
        ->and($content->bride_name)->toBe('')
        ->and($content->groom_name)->toBe('');
});

test('invitation is created from preview data after template payment', function () {
    $previewData = [
        'bride' => [
            'name' => 'Sarah',
            'father' => 'Bapak A',
            'mother' => 'Ibu B',
        ],
        'groom' => [
            'name' => 'John',
            'father' => 'Bapak C',
            'mother' => 'Ibu D',
        ],
        'event' => [
            'date' => '2026-12-25',
            'time' => '10:00',
            'location' => 'Grand Ballroom',
            'address' => 'Jl. Sudirman No. 1',
            'maps_url' => 'https://maps.google.com/test',
        ],
        'story' => 'Our love story...',
        'gift' => [
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'John & Sarah',
        ],
    ];

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($this->user, $this->template, null, $previewData);
    $orderService->updateOrderStatus($order, 'paid');

    $invitation = Invitation::where('user_id', $this->user->id)->first();
    expect($invitation)->not->toBeNull()
        ->and($invitation->template_id)->toBe($this->template->id)
        ->and($invitation->status)->toBe('draft');

    $content = $invitation->content;
    expect($content)->not->toBeNull()
        ->and($content->bride_name)->toBe('Sarah')
        ->and($content->groom_name)->toBe('John')
        ->and($content->akad_venue)->toBe('Grand Ballroom')
        ->and($content->akad_maps_url)->toBe('https://maps.google.com/test')
        ->and($content->love_story)->toBe('Our love story...');
});

test('invitation subdomains are unique across templates', function () {
    $secondTemplate = Template::factory()->create(['price' => 80000]);
    $previewData = [
        'bride' => ['name' => 'Sarah'],
        'groom' => ['name' => 'John'],
    ];

    $orderService = app(OrderService::class);

    $order1 = $orderService->createOrder($this->user, $this->template, null, $previewData);
    $orderService->updateOrderStatus($order1, 'paid');

    $order2 = $orderService->createOrder($this->user, $secondTemplate, null, $previewData);
    $orderService->updateOrderStatus($order2, 'paid');

    $invitations = Invitation::where('user_id', $this->user->id)->get();
    expect($invitations)->toHaveCount(2)
        ->and($invitations[0]->subdomain)->not->toBe($invitations[1]->subdomain);
});

test('template sections are copied to invitation', function () {
    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($this->user, $this->template, null);
    $orderService->updateOrderStatus($order, 'paid');

    $invitation = Invitation::where('user_id', $this->user->id)->first();

    expect($invitation->sections)->toHaveCount($this->template->sections->count());
});

test('template ornaments are copied to invitation', function () {
    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($this->user, $this->template, null);
    $orderService->updateOrderStatus($order, 'paid');

    $invitation = Invitation::where('user_id', $this->user->id)->first();

    expect($invitation->ornaments)->toHaveCount($this->template->ornaments->count());
});
