<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\FortifyServiceProvider;
// use App\Providers\WindowsBladeCompilerServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    FortifyServiceProvider::class,
    // WindowsBladeCompilerServiceProvider::class, // Disabled - causing issues with Filament
];
