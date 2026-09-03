<?php

use App\Services\CloudMailMailer;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('cache.default', 'array');
    setCloudMailConfig();
});

test('sends through the api using the resolved sender account', function () {
    fakeCloudMailApi();

    app(CloudMailMailer::class)->send('no-reply@akadnya.com', [
        ['email' => 'user@example.com', 'name' => 'Budi'],
    ], 'Hello', '<p>Hi</p>', 'Hi');

    Http::assertSentInOrder([
        fn ($request) => str_contains($request->url(), '/login') && $request['email'] === 'service@akadnya.com',
        fn ($request) => str_contains($request->url(), '/account/list'),
        function ($request) {
            return str_contains($request->url(), '/email/send')
                && $request['accountId'] === 11
                && $request['receiveEmail'] === ['user@example.com']
                && $request['subject'] === 'Hello'
                && $request['content'] === '<p>Hi</p>'
                && $request['text'] === 'Hi';
        },
    ]);
});

test('authenticates with the raw jwt in the authorization header', function () {
    fakeCloudMailApi();

    app(CloudMailMailer::class)->sendAs('payment', [
        ['email' => 'user@example.com'],
    ], 'Paid', '<p>Thanks</p>');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/email/send')
            && $request->header('Authorization') === ['jwt-test-token']
            && $request['accountId'] === 33;
    });
});

test('caches the jwt and reuses it across sends', function () {
    $logins = 0;

    Http::fake([
        '*/login' => function () use (&$logins) {
            $logins++;

            return Http::response(['code' => 200, 'data' => ['token' => 'jwt-test-token']]);
        },
        '*/account/list*' => Http::response([
            'code' => 200,
            'data' => ['list' => [
                ['accountId' => 11, 'email' => 'no-reply@akadnya.com', 'name' => 'Akadnya.com'],
            ]],
        ]),
        '*/email/send' => Http::response(['code' => 200, 'data' => []]),
    ]);

    $mailer = app(CloudMailMailer::class);

    $mailer->send('no-reply@akadnya.com', [['email' => 'a@example.com']], 'One', '<p>1</p>');
    $mailer->send('no-reply@akadnya.com', [['email' => 'b@example.com']], 'Two', '<p>2</p>');

    expect($logins)->toBe(1);
});

test('re-authenticates and retries when the token is rejected', function () {
    $logins = 0;
    $sendAttempts = 0;

    Http::fake([
        '*/login' => function () use (&$logins) {
            $logins++;

            return Http::response(['code' => 200, 'data' => ['token' => "jwt-{$logins}"]]);
        },
        '*/account/list*' => Http::response([
            'code' => 200,
            'data' => ['list' => [
                ['accountId' => 11, 'email' => 'no-reply@akadnya.com', 'name' => 'Akadnya.com'],
            ]],
        ]),
        '*/email/send' => function () use (&$sendAttempts) {
            $sendAttempts++;

            return $sendAttempts === 1
                ? Http::response(['code' => 401, 'message' => 'Authentication has expired'], 401)
                : Http::response(['code' => 200, 'data' => []]);
        },
    ]);

    app(CloudMailMailer::class)->send('no-reply@akadnya.com', [['email' => 'a@example.com']], 'S', '<p/>');

    expect($sendAttempts)->toBe(2)->and($logins)->toBe(2);
});

test('throws when the named sender is not configured', function () {
    fakeCloudMailApi();

    expect(fn () => app(CloudMailMailer::class)->sendAs('notif', [['email' => 'a@example.com']], 'S', '<p/>'))
        ->toThrow(RuntimeException::class, 'CloudMail sender [notif] is not configured.');
});

test('throws when the sender account does not exist on cloudmail', function () {
    fakeCloudMailApi();

    expect(fn () => app(CloudMailMailer::class)->send('ghost@akadnya.com', [['email' => 'a@example.com']], 'S', '<p/>'))
        ->toThrow(RuntimeException::class, '[ghost@akadnya.com] was not found');
});

test('throws when the api responds with a business error', function () {
    Http::fake([
        '*/login' => Http::response(['code' => 200, 'data' => ['token' => 'jwt-test-token']]),
        '*/account/list*' => Http::response([
            'code' => 200,
            'data' => ['list' => [
                ['accountId' => 11, 'email' => 'no-reply@akadnya.com', 'name' => 'Akadnya.com'],
            ]],
        ]),
        '*/email/send' => Http::response(['code' => 403, 'message' => 'bannedSend']),
    ]);

    expect(fn () => app(CloudMailMailer::class)->send('no-reply@akadnya.com', [['email' => 'a@example.com']], 'S', '<p/>'))
        ->toThrow(RuntimeException::class, 'bannedSend');
});

test('enabled mirrors the config flag', function () {
    expect(app(CloudMailMailer::class)->enabled())->toBeTrue();

    config()->set('services.cloudmail.enabled', false);

    expect(app(CloudMailMailer::class)->enabled())->toBeFalse();
});
