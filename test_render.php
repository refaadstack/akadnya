<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Template;
use App\Services\BladeRenderService;
use App\Services\DataContractBuilder;

$template = Template::where('slug', 'china-bangka')->first();
$renderer = app(BladeRenderService::class);
$builder = app(DataContractBuilder::class);

$html = $renderer->renderPreview($template, $builder->buildDummy());

file_put_contents('china_bangka_debug.html', $html);

echo "Saved to china_bangka_debug.html\n";
echo "File size: " . strlen($html) . " bytes\n";
echo "Number of sections: " . substr_count($html, 'template-section') . "\n";
