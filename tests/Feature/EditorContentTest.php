<?php

use App\Models\Invitation;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
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
});

test('editor page exposes couple photo, music title and gift address', function () {
    $this->invitation->content()->updateOrCreate(
        ['invitation_id' => $this->invitation->id],
        [
            'couple_photo_url' => 'https://example.com/couple.jpg',
            'music_title' => 'Sepanjang Hidup - Maher Zain',
            'gift_address' => 'Jl. Melati No. 12, Jambi',
        ]
    );

    $this->actingAs($this->user)
        ->get(route('dashboard.editor'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('content.couple_photo_url', 'https://example.com/couple.jpg')
            ->where('content.music_title', 'Sepanjang Hidup - Maher Zain')
            ->where('content.gift_address', 'Jl. Melati No. 12, Jambi')
        );
});

test('editor save persists couple photo, music title and gift address', function () {
    $this->actingAs($this->user)
        ->post(route('dashboard.editor.save'), [
            'bride_name' => 'Siti',
            'groom_name' => 'Budi',
            'akad_datetime' => '2026-12-25T09:00',
            'akad_venue' => 'Masjid Al-Ikhlas',
            'couple_photo_url' => 'https://example.com/couple.jpg',
            'music_title' => 'Sepanjang Hidup - Maher Zain',
            'gift_address' => 'Jl. Melati No. 12, Jambi',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $content = $this->invitation->fresh()->content;
    expect($content->couple_photo_url)->toBe('https://example.com/couple.jpg')
        ->and($content->music_title)->toBe('Sepanjang Hidup - Maher Zain')
        ->and($content->gift_address)->toBe('Jl. Melati No. 12, Jambi')
        ->and($content->bride_name)->toBe('Siti');
});

test('editor save rejects invalid couple photo url', function () {
    $this->actingAs($this->user)
        ->post(route('dashboard.editor.save'), [
            'couple_photo_url' => 'not-a-url',
        ])
        ->assertSessionHasErrors('couple_photo_url');
});

test('editor page exposes show_reception flag', function () {
    $this->invitation->content()->updateOrCreate(
        ['invitation_id' => $this->invitation->id],
        [
            'bride_name' => 'Siti',
            'groom_name' => 'Budi',
            'akad_datetime' => '2026-12-25T09:00',
            'show_reception' => false,
        ]
    );

    $this->actingAs($this->user)
        ->get(route('dashboard.editor'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('content.show_reception', false));
});

test('editor save persists show_reception flag', function () {
    $this->actingAs($this->user)
        ->post(route('dashboard.editor.save'), [
            'bride_name' => 'Siti',
            'groom_name' => 'Budi',
            'akad_datetime' => '2026-12-25T09:00',
            'akad_venue' => 'Masjid Al-Ikhlas',
            'show_reception' => false,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $content = $this->invitation->fresh()->content;
    expect($content->show_reception)->toBeFalse();
});
