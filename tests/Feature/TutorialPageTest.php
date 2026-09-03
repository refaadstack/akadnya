<?php

use App\Models\SiteSetting;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    SiteSetting::flush();
});

test('tutorial page renders with all menu groups', function () {
    $this->get('/tutorial')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tutorial')
            ->has('groups', 3)
            ->has('groups.0.menus', 5)
            ->has('groups.1.menus', 10)
            ->has('groups.2.menus', 3)
            ->where('support.email', 'support@akadnya.com')
            ->where('support.whatsapp', '6282374338273'));
});

test('tutorial page covers every public and dashboard menu', function () {
    $menus = collect($this->get('/tutorial')->assertOk()->viewData('page')['props']['groups'])
        ->flatMap(fn ($group) => $group['menus'])
        ->pluck('name')
        ->all();

    expect($menus)->toContain(
        'Koleksi',
        'Produk',
        'Cara Pesan',
        'FAQ',
        'Masuk / Mulai Desain',
        'Dashboard',
        'Editor',
        'Kustomisasi',
        'Galeri',
        'Tamu',
        'RSVP',
        'Buku Tamu',
        'Love Story',
        'Pengaturan',
        'Transaksi',
        'Keranjang & Checkout',
        'Undangan Publik',
        'Footer & Legal',
    );
});

test('tutorial page shows step by step content', function () {
    $this->get('/tutorial')
        ->assertOk()
        ->assertSee('Panduan Menggunakan Seluruh Menu', false)
        ->assertSee('Publikasikan undangan', false)
        ->assertSee('Check-in tamu dengan QR code', false);
});