<?php

use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Notifications\Channels\BrevoMailChannel;
use App\Notifications\Channels\CloudMailMailChannel;
use App\Notifications\PaymentSuccessfulNotification;
use App\Notifications\VerifyEmailNotification;
use App\Services\OrderService;
use Illuminate\Support\Facades\Http;
use Laravel\Fortify\Features;

beforeEach(function () {
    config()->set('cache.default', 'array');
});

test('verification email goes through cloudmail when enabled', function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());

    setCloudMailConfig();
    fakeCloudMailApi();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect('/');

    Http::assertSent(function ($request) use ($user) {
        return str_contains($request->url(), '/email/send')
            && $request['accountId'] === 22
            && $request['receiveEmail'] === [$user->email]
            && $request['subject'] === 'Verifikasi Email MyAkad';
    });
});

test('verification email falls back to brevo when cloudmail is disabled', function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());

    config()->set('services.cloudmail.enabled', false);

    $user = User::factory()->unverified()->create();

    expect((new VerifyEmailNotification)->via($user))->toBe([BrevoMailChannel::class]);
});

function createPaidOrder(): array
{
    $user = User::factory()->create();
    $template = Template::factory()->create(['price' => 100000]);
    $basePackage = Product::factory()->create([
        'type' => 'base_package',
        'slug' => 'base',
        'price' => 50000,
    ]);

    $order = app(OrderService::class)->createOrder($user, $template, $basePackage);

    return [$user, $order];
}

test('payment notification selects the channel based on the cloudmail flag', function () {
    [$user, $order] = createPaidOrder();

    config()->set('services.cloudmail.enabled', true);
    expect((new PaymentSuccessfulNotification($order))->via($user))->toBe([CloudMailMailChannel::class]);

    config()->set('services.cloudmail.enabled', false);
    expect((new PaymentSuccessfulNotification($order))->via($user))->toBe([BrevoMailChannel::class]);
});

test('payment notification builds a cloudmail payload with the payment sender', function () {
    [$user, $order] = createPaidOrder();

    $payload = (new PaymentSuccessfulNotification($order))->toCloudMailMail($user);

    expect($payload['sender'])->toBe('payment')
        ->and($payload['subject'])->toBe('Pembayaran MyAkad Berhasil')
        ->and($payload['htmlContent'])->toContain($order->order_number)
        ->and($payload['textContent'])->toContain($order->order_number);
});
