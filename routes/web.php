<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Dashboard\LoveStoryController;
use App\Http\Controllers\Dashboard\RsvpController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\GrantController;
use App\Http\Controllers\GuestBookController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\InvitationSettingsController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PaymentFinishController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicInvitationController;
use App\Http\Controllers\TemplateAssetController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplatePreviewController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('sitemap');

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/faq', [FaqController::class, 'index'])->name('faq');
Route::get('/tutorial', [TutorialController::class, 'index'])->name('tutorial');
Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');

// Health check (public, for load balancer / uptime monitoring)
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();

        return response()->json([
            'status' => 'ok',
            'database' => 'ok',
            'time' => now()->toIso8601String(),
        ]);
    } catch (Throwable $e) {
        return response()->json([
            'status' => 'degraded',
            'database' => 'error',
            'time' => now()->toIso8601String(),
        ], 503);
    }
})->name('health');

// Public invitation routes (must be before other routes to catch subdomains)
Route::get('/i/{subdomain}', [PublicInvitationController::class, 'show'])->name('invitation.show');
Route::post('/i/{subdomain}/rsvp', [PublicInvitationController::class, 'rsvp'])->name('invitation.rsvp');
Route::get('/i/{subdomain}/wishes', [PublicInvitationController::class, 'wishes'])->name('invitation.wishes');

// API routes for invitations
Route::get('/api/invitations/{invitationId}/wishes', [PublicInvitationController::class, 'wishesByInvitationId'])->name('api.invitation.wishes');

// Public template routes (templates now in storage, no conflict)
Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
Route::get('/templates/{slug}', [TemplateController::class, 'show'])->name('templates.show');
Route::get('/templates/{slug}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
Route::get('/templates/{slug}/render', [TemplateController::class, 'render'])->name('templates.render');

// Public products routes (à la carte)
Route::get('/produk', [ProductController::class, 'index'])->name('products.index');

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

    // Shopping cart (server-side, requires login)
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/keranjang/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/keranjang', [CartController::class, 'clear'])->name('cart.clear');

    // Activate a template covered by an admin grant (free access)
    Route::post('/grants/activate/{template}', [GrantController::class, 'activate'])->name('grants.activate');
});

// Payment finish page (public, shows order status)
Route::get('/payment/finish', [PaymentFinishController::class, '__invoke'])->name('payment.finish');

// Dashboard routes (requires authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/transactions', [TransactionHistoryController::class, 'index'])->name('dashboard.transactions');
    Route::post('dashboard/invitations/{invitation}/select', [DashboardController::class, 'selectInvitation'])->name('dashboard.invitations.select');
    Route::post('dashboard/templates/{template}/select', [DashboardController::class, 'selectTemplate'])->name('dashboard.templates.select');

    // Admin panel is now handled by Filament at /admin

    // These routes require an active invitation (template purchased) or active package
    Route::middleware('has.invitation.access')->group(function () {
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

        // RSVP management (list lives as a tab on the Guests page)
        Route::get('dashboard/rsvp', [RsvpController::class, 'index'])->name('dashboard.rsvp');
        Route::post('dashboard/rsvp/{rsvp}/hide', [RsvpController::class, 'hide'])->name('dashboard.rsvp.hide');
        Route::post('dashboard/rsvp/{rsvp}/show', [RsvpController::class, 'show'])->name('dashboard.rsvp.show');
        Route::post('dashboard/rsvp/{rsvp}/link', [RsvpController::class, 'link'])->name('dashboard.rsvp.link');

        // Guest book (venue)
        Route::get('dashboard/guest-book', [GuestBookController::class, 'index'])->name('dashboard.guest-book');
        Route::get('dashboard/guest-book/scan', [GuestBookController::class, 'scan'])->name('dashboard.guest-book.scan');
        Route::post('dashboard/guest-book/check-in', [GuestBookController::class, 'checkIn'])->name('dashboard.guest-book.check-in');
        Route::post('dashboard/guest-book/souvenir', [GuestBookController::class, 'souvenir'])->name('dashboard.guest-book.souvenir');
        Route::post('dashboard/guest-book/raffle', [GuestBookController::class, 'raffle'])->name('dashboard.guest-book.raffle');

        // Love Story
        Route::get('dashboard/love-story', [LoveStoryController::class, 'index'])->name('dashboard.love-story');
        Route::post('dashboard/love-story', [LoveStoryController::class, 'update'])->name('dashboard.love-story.update');

        // Media uploads
        Route::post('media/upload/cover', [MediaController::class, 'uploadCover'])->name('media.upload.cover');
        Route::post('media/upload/gallery', [MediaController::class, 'uploadGallery'])->name('media.upload.gallery');
        Route::post('media/upload/music', [MediaController::class, 'uploadMusic'])->name('media.upload.music');
        Route::post('media/upload/qris', [MediaController::class, 'uploadQris'])->name('media.upload.qris');
        Route::post('media/upload/bride', [MediaController::class, 'uploadBridePhoto'])->name('media.upload.bride');
        Route::post('media/upload/groom', [MediaController::class, 'uploadGroomPhoto'])->name('media.upload.groom');
        Route::post('media/upload/couple', [MediaController::class, 'uploadCouplePhoto'])->name('media.upload.couple');
        Route::post('media/upload/background', [MediaController::class, 'uploadBackground'])->name('media.upload.background');
    });
});

require __DIR__.'/settings.php';
