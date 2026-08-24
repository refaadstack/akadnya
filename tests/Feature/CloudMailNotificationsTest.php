<?php

use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Notifications\Channels\CloudMailMailChannel;
use App\Notifications\PaymentSuccessfulNotification;
use App\Notifications\VerifyEmailNotification;
use App\Services\CloudMailMailer;
use App\Services\OrderService;
use Illuminate\Support\Facades\Http;
use Illuminate\Notifications\Channels\MailChannel;
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

test('notifications select the channel based on the enabled flag', function () {
    $user = User::factory()->create();
    $order = createPaidOrder()[1];

    config()->set('services.cloudmail.enabled', true);
    expect((new VerifyEmailNotification)->via($user))->toBe([CloudMailMailChannel::class]);
    expect((new PaymentSuccessfulNotification($order))->via($user))->toBe([CloudMailMailChannel::class]);

    config()->set('services.cloudmail.enabled', false);
    expect((new VerifyEmailNotification)->via($user))->toBe([MailChannel::class]);
    expect((new PaymentSuccessfulNotification($order))->via($user))->toBe([MailChannel::class]);
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

test('payment notification selects the cloudmail channel', function () {
    config()->set('services.cloudmail.enabled', true);

    [$user, $order] = createPaidOrder();

    expect((new PaymentSuccessfulNotification($order))->via($user))->toBe([CloudMailMailChannel::class]);
});

test('payment notification builds a cloudmail payload with the payment sender', function () {
    [$user, $order] = createPaidOrder();

    $payload = (new PaymentSuccessfulNotification($order))->toCloudMailMail($user);

    expect($payload['sender'])->toBe('payment')
        ->and($payload['subject'])->toBe('Pembayaran MyAkad Berhasil')
        ->and($payload['htmlContent'])->toContain($order->order_number)
        ->and($payload['textContent'])->toContain($order->order_number);
});

test('registration still succeeds when cloudmail rejects the verification email', function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());

    setCloudMailConfig();
    fakeFailingCloudMailSend();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');
    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

test('cloudmail channel swallows transport failures so auth flows never break', function () {
    setCloudMailConfig();

    $mailer = Mockery::mock(CloudMailMailer::class);
    $mailer->shouldReceive('sendAs')
        ->once()
        ->andThrow(new RuntimeException('CloudMail API error: destination address is not a verified address'));

    $user = User::factory()->create();

    (new CloudMailMailChannel($mailer))->send($user, new VerifyEmailNotification);

    expect(true)->toBeTrue();
});
