<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Product;
use App\Models\Rsvp;
use App\Models\Template;
use App\Models\User;
use App\Services\OrderService;

function inviteWithWishesToggle(bool $showWishes): Invitation
{
    $user = User::factory()->create();
    $product = Product::factory()->create(['type' => 'addon', 'slug' => 'guest_book']);
    $order = app(OrderService::class)->createOrder($user, null, $product);
    app(OrderService::class)->updateOrderStatus($order, 'paid');

    $template = Template::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
        'subdomain' => 'wishes-'.strtolower(str()->random(6)),
    ]);
    $invitation->content()->updateOrCreate([], ['show_wishes' => $showWishes]);

    Guest::create([
        'invitation_id' => $invitation->id,
        'name' => 'Budi Santoso',
        'category' => 'family',
        'max_pax' => 2,
    ]);

    return $invitation;
}

test('rsvp stores the guest message when wishes are shown', function () {
    $invitation = inviteWithWishesToggle(true);
    $guest = $invitation->guests()->where('name', 'Budi Santoso')->firstOrFail();

    $response = $this
        ->withSession(['invitation_guest.'.$invitation->id => $guest->id])
        ->post("/i/{$invitation->subdomain}/rsvp", [
            'name' => 'Tamu Baik',
            'attendance' => 'yes',
            'pax_count' => 2,
            'message' => 'Selamat menempati hidup baru!',
        ]);

    $response->assertRedirect();

    $rsvp = Rsvp::where('invitation_id', $invitation->id)->first();
    expect($rsvp)->not->toBeNull();
    expect($rsvp->guest_id)->toBe($guest->id);
    expect($rsvp->message)->toBe('Selamat menempati hidup baru!');
});

test('rsvp drops the message when the couple hides wishes', function () {
    $invitation = inviteWithWishesToggle(false);
    $guest = $invitation->guests()->where('name', 'Budi Santoso')->firstOrFail();

    $response = $this
        ->withSession(['invitation_guest.'.$invitation->id => $guest->id])
        ->post("/i/{$invitation->subdomain}/rsvp", [
            'name' => 'Tamu Isekai',
            'attendance' => 'no',
            'message' => '<script>alert("spam")</script> promosi judi',
        ]);

    $response->assertRedirect();

    $rsvp = Rsvp::where('invitation_id', $invitation->id)->first();
    expect($rsvp)->not->toBeNull();
    expect($rsvp->attendance)->toBe('no');
    expect($rsvp->message)->toBeNull();
});

test('data contract exposes show_wishes', function () {
    $invitation = inviteWithWishesToggle(false);
    $contract = app(App\Services\DataContractBuilder::class)->build($invitation, null, null);

    expect($contract['show_wishes'])->toBeFalse();
});
