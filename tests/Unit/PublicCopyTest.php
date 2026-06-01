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
    $pagePaths = [
        dirname(__DIR__, 2).'/resources/js/pages/auth/Login.vue',
        dirname(__DIR__, 2).'/resources/js/pages/auth/Register.vue',
        dirname(__DIR__, 2).'/resources/js/pages/Dashboard.vue',
    ];

    $legacyClasses = [
        'from-pink-50',
        'to-purple-50',
        'from-pink-600',
        'to-purple-600',
        'rounded-2xl',
    ];

    foreach ($pagePaths as $path) {
        $contents = file_get_contents($path);

        expect($contents)->not->toBeFalse();
        expect($contents)->toContain('my-page');

        foreach ($legacyClasses as $legacyClass) {
            expect($contents)->not->toContain($legacyClass);
        }
    }
});
