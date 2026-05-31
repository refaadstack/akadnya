<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dev\PaymentSimulatorController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\InvitationSettingsController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\PublicInvitationController;
use App\Http\Controllers\TemplateAssetController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplatePreviewController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/faq', [FaqController::class, 'index'])->name('faq');

// Public invitation routes (must be before other routes to catch subdomains)
Route::get('/i/{subdomain}', [PublicInvitationController::class, 'show'])->name('invitation.show');
Route::post('/i/{subdomain}/rsvp', [PublicInvitationController::class, 'rsvp'])->name('invitation.rsvp');
Route::get('/i/{subdomain}/wishes', [PublicInvitationController::class, 'wishes'])->name('invitation.wishes');

// API routes for invitations
Route::get('/api/invitations/{invitationId}/wishes', [PublicInvitationController::class, 'wishesByInvitationId'])->name('api.invitation.wishes');

// Public template routes (templates now in storage, no conflict)
Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
Route::get('/templates/{slug}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
Route::get('/templates/{slug}/render', [TemplateController::class, 'render'])->name('templates.render');

// Serve template assets (CSS, JS, images) from storage via Laravel
Route::get('/template-assets/{slug}/{file}', TemplateAssetController::class)
    ->where('file', '.*')
    ->name('templates.asset');

// Template preview API (public, no auth required)
Route::post('/api/templates/{slug}/preview', [TemplatePreviewController::class, 'render'])
    ->name('api.templates.preview');

// Checkout routes (requires authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

// Midtrans webhook (no CSRF, no auth)
Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle'])
    ->name('webhook.midtrans')
    ->withoutMiddleware([ValidateCsrfToken::class]);

// Development tools (only in local environment)
if (app()->environment('local')) {
    Route::prefix('dev')->group(function () {
        Route::get('/payment-simulator', [PaymentSimulatorController::class, 'index'])
            ->name('dev.payment-simulator');
        Route::post('/payment-simulator/success', [PaymentSimulatorController::class, 'simulateSuccess'])
            ->name('dev.payment-simulator.success');
        Route::post('/payment-simulator/failure', [PaymentSimulatorController::class, 'simulateFailure'])
            ->name('dev.payment-simulator.failure');
        Route::post('/payment-simulator/expired', [PaymentSimulatorController::class, 'simulateExpired'])
            ->name('dev.payment-simulator.expired');
    });
}

// Dashboard routes (requires authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin panel is now handled by Filament at /admin

    // These routes require base package
    Route::middleware('has.base.package')->group(function () {
        // Editor
        Route::get('dashboard/editor', [EditorController::class, 'index'])->name('dashboard.editor');
        Route::post('dashboard/editor', [EditorController::class, 'save'])->name('dashboard.editor.save');
        Route::get('dashboard/editor/preview', [EditorController::class, 'preview'])->name('dashboard.editor.preview');

        // Settings (Domain & Publish)
        Route::get('dashboard/settings', [InvitationSettingsController::class, 'index'])->name('dashboard.settings');
        Route::post('dashboard/settings/subdomain', [InvitationSettingsController::class, 'updateSubdomain'])->name('dashboard.settings.subdomain');
        Route::post('dashboard/settings/custom-domain', [InvitationSettingsController::class, 'updateCustomDomain'])->name('dashboard.settings.custom-domain');
        Route::post('dashboard/settings/publish', [InvitationSettingsController::class, 'publish'])->name('dashboard.settings.publish');
        Route::post('dashboard/settings/unpublish', [InvitationSettingsController::class, 'unpublish'])->name('dashboard.settings.unpublish');
        Route::post('dashboard/settings/generate-subdomain', [InvitationSettingsController::class, 'generateSubdomain'])->name('dashboard.settings.generate-subdomain');

        // Invitation customization
        Route::get('dashboard/customize', [InvitationController::class, 'customize'])->name('dashboard.customize');
        Route::post('dashboard/sections/reorder', [InvitationController::class, 'reorderSections'])->name('dashboard.sections.reorder');
        Route::post('dashboard/sections/{section}/toggle', [InvitationController::class, 'toggleSection'])->name('dashboard.sections.toggle');
        Route::post('dashboard/ornaments/{ornament}/toggle', [InvitationController::class, 'toggleOrnament'])->name('dashboard.ornaments.toggle');

        // Gallery management
        Route::get('dashboard/gallery', [GalleryController::class, 'index'])->name('dashboard.gallery');
        Route::post('dashboard/gallery', [GalleryController::class, 'store'])->name('dashboard.gallery.store');
        Route::post('dashboard/gallery/{photo}', [GalleryController::class, 'update'])->name('dashboard.gallery.update');
        Route::delete('dashboard/gallery/{photo}', [GalleryController::class, 'destroy'])->name('dashboard.gallery.destroy');
        Route::post('dashboard/gallery/reorder', [GalleryController::class, 'reorder'])->name('dashboard.gallery.reorder');

        // Publishing
        Route::post('dashboard/publish', [InvitationController::class, 'publish'])->name('dashboard.publish');
        Route::post('dashboard/unpublish', [InvitationController::class, 'unpublish'])->name('dashboard.unpublish');

        // Guest management
        Route::get('dashboard/guests', [GuestController::class, 'index'])->name('dashboard.guests');
        Route::post('dashboard/guests', [GuestController::class, 'store'])->name('dashboard.guests.store');
        Route::put('dashboard/guests/{guest}', [GuestController::class, 'update'])->name('dashboard.guests.update');
        Route::delete('dashboard/guests/{guest}', [GuestController::class, 'destroy'])->name('dashboard.guests.destroy');
        Route::post('dashboard/guests/import', [GuestController::class, 'import'])->name('dashboard.guests.import');
        Route::get('dashboard/guests/export', [GuestController::class, 'export'])->name('dashboard.guests.export');
        Route::get('dashboard/guests/{guest}/whatsapp', [GuestController::class, 'sendWhatsApp'])->name('dashboard.guests.whatsapp');

        // Media uploads
        Route::post('media/upload/cover', [MediaController::class, 'uploadCover'])->name('media.upload.cover');
        Route::post('media/upload/gallery', [MediaController::class, 'uploadGallery'])->name('media.upload.gallery');
        Route::post('media/upload/music', [MediaController::class, 'uploadMusic'])->name('media.upload.music');
        Route::post('media/upload/qris', [MediaController::class, 'uploadQris'])->name('media.upload.qris');
        Route::post('media/upload/bride', [MediaController::class, 'uploadBridePhoto'])->name('media.upload.bride');
        Route::post('media/upload/groom', [MediaController::class, 'uploadGroomPhoto'])->name('media.upload.groom');
    });
});

require __DIR__.'/settings.php';
