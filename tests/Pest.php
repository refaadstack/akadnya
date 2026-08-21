<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function setCloudMailConfig(): void
{
    config()->set('services.cloudmail', [
        'enabled' => true,
        'base_url' => 'https://mail.example.com/api',
        'email' => 'service@myakad.id',
        'password' => 'secret',
        'timeout' => 5,
        'cache_ttl' => 3600,
        'senders' => [
            'default' => 'no-reply@myakad.id',
            'registration' => 'register@myakad.id',
            'payment' => 'payment@myakad.id',
            'notif' => null,
        ],
    ]);
}

function fakeCloudMailApi(): void
{
    Http::fake([
        '*/login' => Http::response([
            'code' => 200,
            'message' => 'success',
            'data' => ['token' => 'jwt-test-token'],
        ]),
        '*/account/list*' => Http::response([
            'code' => 200,
            'message' => 'success',
            'data' => ['list' => [
                ['accountId' => 11, 'email' => 'no-reply@myakad.id', 'name' => 'MyAkad'],
                ['accountId' => 22, 'email' => 'register@myakad.id', 'name' => 'Registration'],
                ['accountId' => 33, 'email' => 'payment@myakad.id', 'name' => 'Payment'],
            ]],
        ]),
        '*/email/send' => Http::response([
            'code' => 200,
            'message' => 'success',
            'data' => ['emailId' => 99],
        ]),
    ]);
}
