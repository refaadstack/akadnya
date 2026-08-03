<?php

use App\Http\Middleware\HasInvitationAccess;
use App\Models\Invitation;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
uses(RefreshDatabase::class);

test('user without an invitation is redirected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard/editor');

    $response->assertRedirect(route('welcome'));
});

test('user with an invitation can access gated routes', function () {
    $user = User::factory()->create();
    $template = Template::factory()->create();
    Invitation::factory()->for($user)->for($template)->create();

    $response = $this->actingAs($user)->get('/dashboard/editor');

    $response->assertOk();
});

test('user with only a legacy base package feature is redirected', function () {
    $user = User::factory()->create();
    $product = Product::factory()->base()->create();

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($user, null, $product);
    $orderService->updateOrderStatus($order, 'paid');

    $response = $this->actingAs($user)->get('/dashboard/editor');

    $response->assertRedirect(route('welcome'));
});

test('unauthenticated user is redirected to login', function () {
    $middleware = new HasInvitationAccess;
    $request = Request::create('/dashboard/editor');
    $request->setUserResolver(fn () => null);

    $response = $middleware->handle($request, fn () => new Response('next'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toBe(route('login'));
});
