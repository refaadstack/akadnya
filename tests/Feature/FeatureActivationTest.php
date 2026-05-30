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
    $this->basePackage = Product::factory()->create([
        'type' => 'base_package',
        'slug' => 'base',
        'price' => 50000,
    ]);
});

test('features are activated after payment', function () {
    $addon = Product::factory()->create([
        'type' => 'addon',
        'slug' => 'custom-domain',
        'price' => 100000,
        'metadata' => [
            'is_recurring' => true,
            'recurring_interval' => 'yearly',
        ],
    ]);

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder(
        $this->user,
        $this->template,
        $this->basePackage,
        [$addon->id]
    );

    // Simulate payment success
    $orderService->updateOrderStatus($order, 'paid');

    // Check user features
    expect(UserFeature::where('user_id', $this->user->id)->count())->toBe(2); // base + addon

    $baseFeature = UserFeature::where('user_id', $this->user->id)
        ->where('feature_slug', 'base')
        ->first();
    expect($baseFeature)->not->toBeNull()
        ->and($baseFeature->is_active)->toBeTrue()
        ->and($baseFeature->expires_at)->toBeNull(); // Base package doesn't expire

    $addonFeature = UserFeature::where('user_id', $this->user->id)
        ->where('feature_slug', 'custom-domain')
        ->first();
    expect($addonFeature)->not->toBeNull()
        ->and($addonFeature->is_active)->toBeTrue()
        ->and($addonFeature->expires_at)->not->toBeNull(); // Addon expires in 1 year
});

test('invitation is created from preview data after payment', function () {
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
    $order = $orderService->createOrder(
        $this->user,
        $this->template,
        $this->basePackage,
        [],
        $previewData
    );

    // Simulate payment success
    $orderService->updateOrderStatus($order, 'paid');

    // Check invitation created
    $invitation = Invitation::where('user_id', $this->user->id)->first();
    expect($invitation)->not->toBeNull()
        ->and($invitation->template_id)->toBe($this->template->id)
        ->and($invitation->status)->toBe('draft')
        ->and($invitation->is_published)->toBeFalse();

    // Check invitation content
    $content = $invitation->content;
    expect($content)->not->toBeNull()
        ->and($content->bride_name)->toBe('Sarah')
        ->and($content->groom_name)->toBe('John')
        ->and($content->event_date)->toBe('2026-12-25')
        ->and($content->event_location)->toBe('Grand Ballroom')
        ->and($content->gift_bank_name)->toBe('BCA');
});

test('invitation slug is unique', function () {
    $previewData = [
        'bride' => ['name' => 'Sarah'],
        'groom' => ['name' => 'John'],
        'event' => ['date' => '2026-12-25'],
    ];

    // Create first invitation
    $orderService = app(OrderService::class);
    $order1 = $orderService->createOrder(
        $this->user,
        $this->template,
        $this->basePackage,
        [],
        $previewData
    );
    $orderService->updateOrderStatus($order1, 'paid');

    // Create second invitation with same name
    $order2 = $orderService->createOrder(
        $this->user,
        $this->template,
        $this->basePackage,
        [],
        $previewData
    );
    $orderService->updateOrderStatus($order2, 'paid');

    $invitations = Invitation::where('user_id', $this->user->id)->get();
    expect($invitations)->toHaveCount(2)
        ->and($invitations[0]->slug)->not->toBe($invitations[1]->slug);
});

test('template sections are copied to invitation', function () {
    $previewData = [
        'bride' => ['name' => 'Sarah'],
        'groom' => ['name' => 'John'],
        'event' => ['date' => '2026-12-25'],
    ];

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder(
        $this->user,
        $this->template,
        $this->basePackage,
        [],
        $previewData
    );
    $orderService->updateOrderStatus($order, 'paid');

    $invitation = Invitation::where('user_id', $this->user->id)->first();

    // Check sections copied
    expect($invitation->sections)->toHaveCount($this->template->sections->count());

    foreach ($this->template->sections as $index => $templateSection) {
        $invitationSection = $invitation->sections[$index];
        expect($invitationSection->section_key)->toBe($templateSection->section_key)
            ->and($invitationSection->html_content)->toBe($templateSection->html_content)
            ->and($invitationSection->order)->toBe($templateSection->order);
    }
});

test('template ornaments are copied to invitation', function () {
    $previewData = [
        'bride' => ['name' => 'Sarah'],
        'groom' => ['name' => 'John'],
        'event' => ['date' => '2026-12-25'],
    ];

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder(
        $this->user,
        $this->template,
        $this->basePackage,
        [],
        $previewData
    );
    $orderService->updateOrderStatus($order, 'paid');

    $invitation = Invitation::where('user_id', $this->user->id)->first();

    // Check ornaments copied
    expect($invitation->ornaments)->toHaveCount($this->template->ornaments->count());

    foreach ($this->template->ornaments as $index => $templateOrnament) {
        $invitationOrnament = $invitation->ornaments[$index];
        expect($invitationOrnament->ornament_key)->toBe($templateOrnament->ornament_key)
            ->and($invitationOrnament->html_content)->toBe($templateOrnament->html_content);
    }
});

test('no invitation created if no preview data', function () {
    $orderService = app(OrderService::class);
    $order = $orderService->createOrder(
        $this->user,
        $this->template,
        $this->basePackage,
        [],
        null // No preview data
    );
    $orderService->updateOrderStatus($order, 'paid');

    // Check no invitation created
    expect(Invitation::where('user_id', $this->user->id)->count())->toBe(0);
});

test('monthly recurring feature expires in 1 month', function () {
    $addon = Product::factory()->create([
        'type' => 'addon',
        'slug' => 'premium-music',
        'metadata' => [
            'is_recurring' => true,
            'recurring_interval' => 'monthly',
        ],
    ]);

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder(
        $this->user,
        $this->template,
        $this->basePackage,
        [$addon->id]
    );
    $orderService->updateOrderStatus($order, 'paid');

    $feature = UserFeature::where('user_id', $this->user->id)
        ->where('feature_slug', 'premium-music')
        ->first();

    expect($feature->expires_at)->not->toBeNull();

    $expectedExpiry = now()->addMonth();
    expect($feature->expires_at->diffInDays($expectedExpiry))->toBeLessThan(1);
});
