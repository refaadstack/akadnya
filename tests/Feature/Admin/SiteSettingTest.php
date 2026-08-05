<?php

use App\Models\SiteSetting;

beforeEach(function () {
    SiteSetting::flush();
});

test('get returns the stored value', function () {
    SiteSetting::set('qr_logo_url', 'https://example.com/logo.svg');

    expect(SiteSetting::get('qr_logo_url'))->toBe('https://example.com/logo.svg');
});

test('get falls back to the default when the key does not exist', function () {
    expect(SiteSetting::get('qr_logo_url'))->toBeNull();
    expect(SiteSetting::get('missing_key', '/favicon.svg'))->toBe('/favicon.svg');
});

test('set updates an existing key', function () {
    SiteSetting::set('qr_logo_url', 'https://example.com/first.svg');
    SiteSetting::set('qr_logo_url', 'https://example.com/second.svg');

    expect(SiteSetting::get('qr_logo_url'))->toBe('https://example.com/second.svg');
    expect(SiteSetting::where('key', 'qr_logo_url')->count())->toBe(1);
});

test('remove deletes the key and refreshes the cache', function () {
    SiteSetting::set('qr_logo_url', 'https://example.com/logo.svg');
    SiteSetting::remove('qr_logo_url');

    expect(SiteSetting::get('qr_logo_url'))->toBeNull();
    expect(SiteSetting::where('key', 'qr_logo_url')->exists())->toBeFalse();
});
