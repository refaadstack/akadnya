<?php

use App\Models\SiteSetting;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    SiteSetting::flush();
});

test('faq page renders with default support contact', function () {
    $this->get('/faq')
        ->assertOk()
        ->assertSee('support@akadnya.com', false)
        ->assertSee('+62 8237-4338-273', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Faq')
            ->has('faqs')
            ->where('support.email', 'support@akadnya.com')
            ->where('support.whatsapp', '6282374338273'));
});

test('faq page states invitations are always active', function () {
    $this->get('/faq')
        ->assertOk()
        ->assertSee('Undangan Anda selalu aktif tanpa batas waktu', false)
        ->assertSee('tidak ada biaya perpanjangan', false);
});

test('faq page uses configurable support contact from site settings', function () {
    SiteSetting::set('support_email', 'halo@example.com');
    SiteSetting::set('support_whatsapp', '6281111111111');

    $this->get('/faq')
        ->assertOk()
        ->assertSee('halo@example.com', false)
        ->assertSee('+62 8111-1111-111', false)
        ->assertInertia(fn (Assert $page) => $page
            ->where('support.email', 'halo@example.com')
            ->where('support.whatsapp', '6281111111111'));
});