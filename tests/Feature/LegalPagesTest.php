<?php

use Inertia\Testing\AssertableInertia as Assert;

test('terms page can be rendered', function () {
    $response = $this->get('/terms');

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Legal/Terms')
        ->has('meta'));
});

test('privacy page can be rendered', function () {
    $response = $this->get('/privacy');

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Legal/Privacy')
        ->has('meta'));
});