<?php

use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Notifications\PaymentSuccessfulNotification;
use App\Notifications\VerifyEmailNotification;
use App\Services\OrderService;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    config()->set('cache.default', 'array');
    config()->set('services.cloudmail.enabled', false);
});

test('verification email falls back to standard smtp when cloudmail is disabled', function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());

    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect('/');

    Notification::assertSentTo($user, VerifyEmailNotification::class, function (
        VerifyEmailNotification $notification,
        array $channels,
    ) {
        return $channels === [MailChannel::class];
    });
});

test('verification mailable contains the signed verification link', function () {
    $user = User::factory()->unverified()->create();

    $mailable = (new VerifyEmailNotification)->toMail($user);

    expect(mailableHtml($mailable))->toContain(url('/'));
});

test('payment mailable keeps its content when using the smtp channel', function () {
    [$user, $order] = createPaidSmtpOrder();

    $mailable = (new PaymentSuccessfulNotification($order))->toMail($user);

    expect($mailable->subject)->toBe('Pembayaran MyAkad Berhasil')
        ->and(mailableHtml($mailable))->toContain($order->order_number);
});

function mailableHtml(Mailable $mailable): string
{
    return (string) Closure::bind(fn () => $this->html, $mailable, $mailable::class)->call($mailable);
}

function createPaidSmtpOrder(): array
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
