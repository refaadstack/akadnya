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
