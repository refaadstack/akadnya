<?php

use Illuminate\Contracts\Console\Kernel;

// Run migrations against the test database
putenv('DB_DATABASE=myakad_test');
$_ENV['DB_DATABASE'] = 'myakad_test';
$_SERVER['DB_DATABASE'] = 'myakad_test';

require '/app/vendor/autoload.php';
$app = require_once '/app/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Override config
config(['database.connections.mysql.database' => 'myakad_test']);

$exitCode = Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();
exit($exitCode);
