<?php

use Illuminate\Support\Facades\File;

test('every invitation template ships the standard RSVP form contract', function () {
    $base = storage_path('app/public/templates');

    $files = array_values(array_filter(array_merge(
        glob($base.'/*/sections/full.html') ?: [],
        [$base.'/_shared/sections/rsvp.html'],
    )));

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $html = File::get($file);
        $label = str_replace($base.'/', '', $file);

        // Standard contract expected by PublicInvitationController@rsvp.
        if (! str_contains($html, '<form')) {
            continue;
        }

        expect($html)->toContain('name="attendance"')
            ->toContain('value="yes"')
            ->toContain('value="no"')
            ->toContain('name="pax_count"')
            ->toContain('name="message"');

        expect(preg_match('/name="status"/', $html))->toBe(0, $label);
        expect(preg_match('/name="pax"(?!_)/', $html))->toBe(0, $label);
    }
});
