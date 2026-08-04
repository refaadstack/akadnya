<?php

it('keeps public landing copy free from developer jargon', function () {
    $publicPagePaths = [
        dirname(__DIR__, 2).'/resources/js/pages/Welcome.vue',
        dirname(__DIR__, 2).'/resources/js/pages/Templates/Index.vue',
    ];

    $forbiddenPhrases = [
        'route render',
        'jalur terpisah',
        'nabrak halaman Vue',
        'Template isolated render',
        'Render preview isolated',
        'Premium Digital Invitation',
    ];

    foreach ($publicPagePaths as $path) {
        $contents = file_get_contents($path);

        expect($contents)->not->toBeFalse();

        foreach ($forbiddenPhrases as $phrase) {
            expect($contents)->not->toContain($phrase);
        }
    }
});

it('keeps auth and dashboard pages on the current visual system', function () {
    $pages = [
        'auth/Login.vue' => 'my-page',
        'auth/Register.vue' => 'my-page',
        'Dashboard.vue' => 'my-btn-primary',
    ];

    $legacyClasses = [
        'from-pink-50',
        'to-purple-50',
        'from-pink-600',
        'to-purple-600',
        'rounded-2xl',
    ];

    foreach ($pages as $page => $marker) {
        $contents = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/'.$page);

        expect($contents)->not->toBeFalse();
        expect($contents)->toContain($marker);

        foreach ($legacyClasses as $legacyClass) {
            expect($contents)->not->toContain($legacyClass);
        }
    }
});
