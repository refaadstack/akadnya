# Design Document: Undangan Digital

## Overview

Undangan Digital adalah aplikasi B2C berbasis Laravel 13 yang memungkinkan pengantin membuat dan mengelola undangan pernikahan digital secara mandiri. Sistem menggunakan arsitektur monolitik dengan isolasi data by user_id, template berbasis folder yang dapat ditambahkan tanpa deployment, dan integrasi payment gateway Midtrans.

### Core Technologies

- **Backend**: Laravel 13 (PHP 8.3)
- **Frontend**: Vue 3 + InertiaJS v3 + TailwindCSS v4
- **Database**: MySQL 8
- **Cache & Queue**: Redis 7 + Laravel Horizon
- **Storage**: Cloudflare R2 (S3-compatible)
- **Payment**: Midtrans
- **Template Engine**: Mustache.js (client-side rendering)
- **Error Tracking**: Sentry
- **Web Server**: Nginx (wildcard subdomain + SSL)

### Key Design Principles

1. **Data Isolation**: All user data isolated by `user_id` - no multi-tenancy, single database
2. **Template Flexibility**: Folder-based templates synced to database via Artisan command
3. **Lifetime Pricing**: One-time payment per invitation, no subscriptions
4. **Preview-First**: Users can preview with dummy or own data before purchasing
5. **Security-First**: MIME validation, rate limiting, signed URLs, webhook verification


## Architecture

### System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         Browser (Guest/User)                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │ Public Pages │  │   Dashboard  │  │  Admin Panel │          │
│  │ (Vue/Inertia)│  │ (Vue/Inertia)│  │  (Filament)  │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Nginx (Wildcard SSL)                          │
│  *.undangan.com → ResolveInvitation Middleware                   │
│  undangan.com → Landing/Preview/Checkout                         │
│  custom-domain.com → ResolveInvitation Middleware                │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Laravel Application                         │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                    HTTP Layer                            │   │
│  │  Controllers → Form Requests → Middleware                │   │
│  └──────────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                   Service Layer                          │   │
│  │  TemplateService │ OrderService │ PaymentService        │   │
│  │  PreviewService  │ MediaService │ InvitationService     │   │
│  └──────────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                    Domain Layer                          │   │
│  │  Models (Eloquent) + Global Scopes + Relationships      │   │
│  └──────────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                   Queue Layer                            │   │
│  │  SendEmailConfirmation │ OptimizeUploadedImage          │   │
│  │  ProcessMidtransWebhook                                  │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
         │                    │                    │
         ▼                    ▼                    ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│    MySQL     │    │    Redis     │    │ Cloudflare R2│
│  (Single DB) │    │ (Cache/Queue)│    │   (Storage)  │
└──────────────┘    └──────────────┘    └──────────────┘
                              │
                              ▼
                    ┌──────────────┐
                    │   Midtrans   │
                    │   (Payment)  │
                    └──────────────┘
```

### Request Flow Examples

#### 1. Guest Accessing Invitation

```
1. Browser → https://justinemma.undangan.com
2. Nginx → Laravel App
3. ResolveInvitation Middleware:
   - Extract subdomain: "justinemma"
   - Query: Invitation::where('subdomain', 'justinemma')
             ->where('status', 'published')->firstOrFail()
   - Set invitation to request context
4. PublicInvitationController:
   - Load invitation_contents
   - Load visible invitation_sections (ordered by sort_order)
   - Load active invitation_ornaments
   - Increment view_count
5. Render template with Mustache.js
6. Return HTML response
```

#### 2. User Editing Content

```
1. Browser → POST /dashboard/editor/content
2. Auth Middleware → verify authenticated
3. HasBasePackage Middleware → verify user has base_package feature
4. EditorController@saveContent:
   - Validate request via ProfileUpdateRequest
   - Load user's invitation (scoped by user_id)
   - Update invitation_contents
   - Return Inertia response with success message
5. Frontend updates UI optimistically
```

#### 3. Payment Webhook Processing

```
1. Midtrans → POST /webhook/midtrans
2. MidtransWebhookController:
   - Verify signature
   - Lock payment record (lockForUpdate)
   - Check if already processed (idempotency)
   - Update payment status to 'paid'
   - Update order status to 'paid'
   - Dispatch ActivateFeaturesJob
3. ActivateFeaturesJob:
   - Create user_features records
   - Create invitation if base_package
   - Restore preview data to invitation_contents
   - Dispatch SendEmailConfirmation
4. Return 200 OK to Midtrans
```

### Layered Architecture

#### Presentation Layer
- **Public Routes**: Landing, template preview, checkout (no auth)
- **Dashboard Routes**: Content editor, guest management, RSVP dashboard (auth + HasBasePackage)
- **Admin Routes**: Filament admin panel (auth + role:admin)
- **API Routes**: Webhook endpoints (signature verification)

#### Application Layer (Services)
- **TemplateService**: Sync folders, parse Mustache, render templates
- **OrderService**: Create orders, calculate totals, activate features
- **PaymentService**: Midtrans integration, webhook processing
- **PreviewService**: Inject dummy/user data into templates
- **MediaService**: Upload to R2, optimize images, generate signed URLs
- **InvitationService**: Publish/unpublish, subdomain generation

#### Domain Layer (Models)
- **User**: Authentication, features relationship
- **Invitation**: Core entity, global scope by user_id
- **Template**: Folder-based templates
- **Order**: Payment transactions
- **Guest**: Invitation recipients
- **RSVP**: Guest confirmations

#### Infrastructure Layer
- **Database**: MySQL with proper indexing
- **Cache**: Redis for session, rate limiting
- **Queue**: Redis + Horizon for async jobs
- **Storage**: R2 for media files
- **External**: Midtrans API


## Components and Interfaces

### Backend Components

#### 1. Template Management

**TemplateService**

```php
class TemplateService
{
    /**
     * Scan storage/templates/ and sync to database
     * 
     * @return array{synced: int, errors: array}
     */
    public function syncTemplates(): array;
    
    /**
     * Parse template.json and validate structure
     * 
     * @param string $folderPath
     * @return array|null Returns parsed data or null if invalid
     */
    public function parseTemplateJson(string $folderPath): ?array;
    
    /**
     * Validate template files exist (sections, ornaments, assets)
     * 
     * @param string $folderPath
     * @param array $templateData
     * @return array{valid: bool, errors: array}
     */
    public function validateTemplateFiles(string $folderPath, array $templateData): array;
    
    /**
     * Render invitation with Mustache engine
     * 
     * @param Invitation $invitation
     * @param array $data
     * @return string Rendered HTML
     */
    public function renderInvitation(Invitation $invitation, array $data): string;
    
    /**
     * Load section HTML file
     * 
     * @param Template $template
     * @param string $sectionFile
     * @return string HTML content with Mustache tags
     */
    public function loadSectionHtml(Template $template, string $sectionFile): string;
    
    /**
     * Load ornament HTML file
     * 
     * @param Template $template
     * @param string $ornamentFile
     * @return string HTML content
     */
    public function loadOrnamentHtml(Template $template, string $ornamentFile): string;
}
```

**SyncTemplates Command**

```php
class SyncTemplates extends Command
{
    protected $signature = 'templates:sync';
    protected $description = 'Sync template folders to database';
    
    public function handle(TemplateService $service): int
    {
        $result = $service->syncTemplates();
        
        $this->info("Synced {$result['synced']} templates");
        
        if (count($result['errors']) > 0) {
            $this->error('Errors:');
            foreach ($result['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }
        
        return self::SUCCESS;
    }
}
```

#### 2. Order and Payment Processing

**OrderService**

```php
class OrderService
{
    /**
     * Create order from checkout data
     * 
     * @param User $user
     * @param array $items [{product_id, quantity, metadata}]
     * @return Order
     */
    public function createOrder(User $user, array $items): Order;
    
    /**
     * Calculate order total from items
     * 
     * @param array $items
     * @return float
     */
    public function calculateTotal(array $items): float;
    
    /**
     * Activate features after payment
     * 
     * @param Order $order
     * @return void
     */
    public function activateFeatures(Order $order): void;
    
    /**
     * Create invitation for user after base_package activation
     * 
     * @param User $user
     * @param Template $template
     * @param array|null $previewData
     * @return Invitation
     */
    public function createInvitation(User $user, Template $template, ?array $previewData = null): Invitation;
    
    /**
     * Generate unique subdomain for invitation
     * 
     * @param string $baseName
     * @return string
     */
    public function generateSubdomain(string $baseName): string;
}
```

**PaymentService**

```php
class PaymentService
{
    /**
     * Request Snap token from Midtrans
     * 
     * @param Order $order
     * @return string Snap token
     * @throws MidtransException
     */
    public function requestSnapToken(Order $order): string;
    
    /**
     * Verify Midtrans webhook signature
     * 
     * @param array $payload
     * @param string $signature
     * @return bool
     */
    public function verifySignature(array $payload, string $signature): bool;
    
    /**
     * Process webhook notification
     * 
     * @param array $notification
     * @return void
     */
    public function processWebhook(array $notification): void;
    
    /**
     * Update payment status
     * 
     * @param Payment $payment
     * @param string $status
     * @param array $rawResponse
     * @return void
     */
    public function updatePaymentStatus(Payment $payment, string $status, array $rawResponse): void;
}
```

#### 3. Media Management

**MediaService**

```php
class MediaService
{
    /**
     * Upload file to R2 storage
     * 
     * @param UploadedFile $file
     * @param User $user
     * @param string $type ('image'|'audio')
     * @return string Public URL
     * @throws InvalidMimeTypeException
     */
    public function upload(UploadedFile $file, User $user, string $type): string;
    
    /**
     * Validate file MIME type using finfo
     * 
     * @param UploadedFile $file
     * @param array $allowedMimes
     * @return bool
     */
    public function validateMimeType(UploadedFile $file, array $allowedMimes): bool;
    
    /**
     * Generate UUID filename
     * 
     * @param string $extension
     * @return string
     */
    public function generateFilename(string $extension): string;
    
    /**
     * Generate temporary signed URL (1 hour expiration)
     * 
     * @param string $path
     * @return string
     */
    public function getSignedUrl(string $path): string;
    
    /**
     * Optimize image (dispatch queue job)
     * 
     * @param string $path
     * @return void
     */
    public function optimizeImage(string $path): void;
    
    /**
     * Delete file from R2
     * 
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool;
}
```

#### 4. Preview System

**PreviewService**

```php
class PreviewService
{
    /**
     * Get dummy data for template preview
     * 
     * @param Template $template
     * @return array
     */
    public function getDummyData(Template $template): array;
    
    /**
     * Validate preview data structure
     * 
     * @param array $data
     * @return bool
     */
    public function validatePreviewData(array $data): bool;
    
    /**
     * Check if preview data is expired (>24 hours)
     * 
     * @param array $data
     * @return bool
     */
    public function isPreviewDataExpired(array $data): bool;
    
    /**
     * Map preview data to invitation_contents columns
     * 
     * @param array $previewData
     * @return array
     */
    public function mapToInvitationContents(array $previewData): array;
}
```

#### 5. Invitation Management

**InvitationService**

```php
class InvitationService
{
    /**
     * Publish invitation
     * 
     * @param Invitation $invitation
     * @return void
     * @throws ValidationException
     */
    public function publish(Invitation $invitation): void;
    
    /**
     * Unpublish invitation
     * 
     * @param Invitation $invitation
     * @return void
     */
    public function unpublish(Invitation $invitation): void;
    
    /**
     * Validate required content fields are filled
     * 
     * @param Invitation $invitation
     * @return array{valid: bool, errors: array}
     */
    public function validateRequiredContent(Invitation $invitation): array;
    
    /**
     * Increment view count
     * 
     * @param Invitation $invitation
     * @return void
     */
    public function incrementViewCount(Invitation $invitation): void;
    
    /**
     * Reorder sections
     * 
     * @param Invitation $invitation
     * @param array $sectionOrder [{id, sort_order}]
     * @return void
     */
    public function reorderSections(Invitation $invitation, array $sectionOrder): void;
    
    /**
     * Toggle section visibility
     * 
     * @param InvitationSection $section
     * @param bool $visible
     * @return void
     * @throws ValidationException If section is required
     */
    public function toggleSectionVisibility(InvitationSection $section, bool $visible): void;
    
    /**
     * Toggle ornament active state
     * 
     * @param InvitationOrnament $ornament
     * @param bool $active
     * @return void
     */
    public function toggleOrnament(InvitationOrnament $ornament, bool $active): void;
}
```

### Middleware Components

#### ResolveInvitation

```php
class ResolveInvitation
{
    /**
     * Resolve invitation from subdomain or custom domain
     * 
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws NotFoundHttpException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $platformDomain = config('app.domain'); // 'undangan.com'
        
        // Check if subdomain
        if (str_ends_with($host, '.' . $platformDomain)) {
            $subdomain = str_replace('.' . $platformDomain, '', $host);
            $invitation = Invitation::where('subdomain', $subdomain)
                ->where('status', 'published')
                ->firstOrFail();
        } else {
            // Custom domain
            $invitation = Invitation::where('custom_domain', $host)
                ->where('status', 'published')
                ->firstOrFail();
        }
        
        // Set invitation to request
        $request->attributes->set('invitation', $invitation);
        
        return $next($request);
    }
}
```

#### HasBasePackage

```php
class HasBasePackage
{
    /**
     * Verify user has active base_package feature
     * 
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws AccessDeniedHttpException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        $hasFeature = UserFeature::where('user_id', $user->id)
            ->where('feature', 'base_package')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
        
        if (!$hasFeature) {
            abort(403, 'Silakan beli paket terlebih dahulu.');
        }
        
        return $next($request);
    }
}
```

### Queue Jobs

#### SendEmailConfirmation

```php
class SendEmailConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public int $tries = 3;
    public int $timeout = 30;
    
    public function __construct(
        public Order $order,
        public string $type // 'payment_success' | 'invitation_published' | 'rsvp_received'
    ) {}
    
    public function handle(): void
    {
        $user = $this->order->user;
        
        match($this->type) {
            'payment_success' => Mail::to($user)->send(new PaymentSuccessMail($this->order)),
            'invitation_published' => Mail::to($user)->send(new InvitationPublishedMail($this->order->user->invitation)),
            'rsvp_received' => Mail::to($user)->send(new RsvpReceivedMail($this->order)),
        };
    }
    
    public function failed(Throwable $exception): void
    {
        Log::error('Email sending failed', [
            'order_id' => $this->order->id,
            'type' => $this->type,
            'error' => $exception->getMessage()
        ]);
    }
}
```

#### OptimizeUploadedImage

```php
class OptimizeUploadedImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public int $tries = 2;
    public int $timeout = 120;
    
    public function __construct(
        public string $path,
        public int $maxWidth = 1920,
        public int $quality = 85
    ) {}
    
    public function handle(): void
    {
        $disk = Storage::disk('r2');
        $tempPath = storage_path('app/temp/' . basename($this->path));
        
        // Download from R2
        file_put_contents($tempPath, $disk->get($this->path));
        
        // Optimize with Intervention Image
        $image = Image::make($tempPath);
        
        if ($image->width() > $this->maxWidth) {
            $image->resize($this->maxWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        
        // Convert to WebP if supported
        $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $tempPath);
        $image->encode('webp', $this->quality)->save($webpPath);
        
        // Upload back to R2
        $disk->put(
            str_replace(['.jpg', '.jpeg', '.png'], '.webp', $this->path),
            file_get_contents($webpPath)
        );
        
        // Cleanup
        unlink($tempPath);
        if (file_exists($webpPath)) {
            unlink($webpPath);
        }
    }
}
```

### Frontend Components (Vue 3 + Inertia)

#### Dashboard Pages

**Pages/Dashboard/Editor.vue**
- Content editor form (bride/groom names, dates, venues, story)
- Media upload (cover photo, gallery, music)
- Section reordering (drag & drop)
- Ornament toggles
- Live preview panel

**Pages/Dashboard/Guests/Index.vue**
- Guest list table with filters
- Add guest form
- CSV import
- Personal link generation with QR code
- Category breakdown statistics

**Pages/Dashboard/RSVP/Index.vue**
- RSVP responses table
- Attendance statistics
- Filter by status and category
- Export to CSV
- Real-time updates

**Pages/Dashboard/Settings.vue**
- Custom domain configuration
- DNS instructions
- Subdomain management
- Feature status display

#### Public Pages

**Pages/Templates/Index.vue**
- Template grid with thumbnails
- Filter by price/category
- Preview modal with dummy data
- "Coba dengan datamu" button

**Pages/Templates/Preview.vue**
- Full-screen template preview
- Data input form (sidebar)
- Real-time Mustache rendering
- Photo upload with URL.createObjectURL()
- "Beli sekarang" button → saves to sessionStorage

**Pages/Checkout/Index.vue**
- Order summary
- Add-on selection
- Preview data display (from sessionStorage)
- Login/register prompt if not authenticated
- Midtrans Snap integration

#### Shared Components

**Components/TemplateRenderer.vue**
```vue
<script setup lang="ts">
import Mustache from 'mustache';
import { computed } from 'vue';

const props = defineProps<{
  sections: Array<{file: string, html: string, sort_order: number}>;
  ornaments: Array<{file: string, html: string, position: string}>;
  data: Record<string, any>;
}>();

const renderedSections = computed(() => {
  return props.sections
    .sort((a, b) => a.sort_order - b.sort_order)
    .map(section => ({
      ...section,
      rendered: Mustache.render(section.html, props.data)
    }));
});

const ornamentsByPosition = computed(() => {
  return {
    top: props.ornaments.filter(o => o.position === 'top'),
    bottom: props.ornaments.filter(o => o.position === 'bottom'),
    between: props.ornaments.filter(o => o.position === 'between'),
    overlay: props.ornaments.filter(o => o.position === 'overlay')
  };
});
</script>

<template>
  <div class="invitation-wrapper">
    <!-- Top ornaments -->
    <div v-for="ornament in ornamentsByPosition.top" :key="ornament.file" 
         v-html="ornament.html" />
    
    <!-- Sections -->
    <div v-for="(section, index) in renderedSections" :key="section.file">
      <div v-html="section.rendered" />
      
      <!-- Between ornaments -->
      <div v-if="index < renderedSections.length - 1"
           v-for="ornament in ornamentsByPosition.between" 
           :key="ornament.file"
           v-html="ornament.html" />
    </div>
    
    <!-- Bottom ornaments -->
    <div v-for="ornament in ornamentsByPosition.bottom" :key="ornament.file"
         v-html="ornament.html" />
    
    <!-- Overlay ornaments -->
    <div class="ornament-overlay">
      <div v-for="ornament in ornamentsByPosition.overlay" :key="ornament.file"
           v-html="ornament.html" />
    </div>
  </div>
</template>
```

**Components/PreviewDataForm.vue**
```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps<{
  templateSlug: string;
}>();

const emit = defineEmits<{
  dataChanged: [data: Record<string, any>];
}>();

const form = useForm({
  bride_name: '',
  groom_name: '',
  event_date: '',
  akad_time: '',
  reception_time: '',
  venue_name: '',
  venue_address: '',
  cover_photo: null as File | null
});

// Watch form changes and emit to parent
watch(() => form.data(), (newData) => {
  const previewData = {
    ...newData,
    template_slug: props.templateSlug,
    preview_at: new Date().toISOString()
  };
  
  // Save to sessionStorage
  sessionStorage.setItem('preview_data', JSON.stringify(previewData));
  
  // Emit to parent for real-time rendering
  emit('dataChanged', previewData);
}, { deep: true });

const handlePhotoUpload = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (file) {
    form.cover_photo = file;
    // Create object URL for preview
    const objectUrl = URL.createObjectURL(file);
    emit('dataChanged', { ...form.data(), cover_photo_url: objectUrl });
  }
};
</script>

<template>
  <form class="space-y-4">
    <div>
      <label>Nama Mempelai Wanita</label>
      <input v-model="form.bride_name" type="text" />
    </div>
    
    <div>
      <label>Nama Mempelai Pria</label>
      <input v-model="form.groom_name" type="text" />
    </div>
    
    <div>
      <label>Tanggal Acara</label>
      <input v-model="form.event_date" type="date" />
    </div>
    
    <div>
      <label>Foto Cover</label>
      <input type="file" accept="image/*" @change="handlePhotoUpload" />
    </div>
    
    <!-- More fields... -->
  </form>
</template>
```


## Data Models

### Database Schema

#### Entity Relationship Diagram

```
users (1) ──────< (M) invitations
  │                      │
  │                      ├──< invitation_contents (1:1)
  │                      ├──< invitation_sections (M)
  │                      ├──< invitation_ornaments (M)
  │                      ├──< invitation_gallery (M)
  │                      └──< guests (M)
  │                              └──< rsvps (1:1)
  │
  ├──< orders (M)
  │      ├──< order_items (M)
  │      └──< payments (M)
  │
  └──< user_features (M)

templates (1) ──< (M) invitations
  ├──< template_sections (M)
  └──< template_ornaments (M)

products (1) ──< (M) order_items
```

#### Table Definitions

**users**
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    email_verified_at TIMESTAMP NULL,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    two_factor_confirmed_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**products**
```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('base_package', 'addon') NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_type (type),
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**templates**
```sql
CREATE TABLE templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    thumbnail_url VARCHAR(500) NULL,
    version VARCHAR(20) DEFAULT '1.0.0',
    is_free BOOLEAN DEFAULT FALSE,
    price DECIMAL(12,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    synced_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**template_sections**
```sql
CREATE TABLE template_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id BIGINT UNSIGNED NOT NULL,
    file VARCHAR(100) NOT NULL,
    label VARCHAR(100) NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL,
    is_required BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE,
    INDEX idx_template (template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**template_ornaments**
```sql
CREATE TABLE template_ornaments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id BIGINT UNSIGNED NOT NULL,
    file VARCHAR(100) NOT NULL,
    label VARCHAR(100) NOT NULL,
    position ENUM('top', 'bottom', 'between', 'overlay') NOT NULL,
    default_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE,
    INDEX idx_template (template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**invitations**
```sql
CREATE TABLE invitations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED NOT NULL,
    subdomain VARCHAR(100) NULL UNIQUE,
    custom_domain VARCHAR(255) NULL UNIQUE,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    view_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE RESTRICT,
    INDEX idx_user (user_id),
    INDEX idx_subdomain (subdomain),
    INDEX idx_custom_domain (custom_domain),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**invitation_contents**
```sql
CREATE TABLE invitation_contents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invitation_id BIGINT UNSIGNED NOT NULL UNIQUE,
    -- Identitas
    bride_name VARCHAR(255) NULL,
    groom_name VARCHAR(255) NULL,
    bride_father VARCHAR(255) NULL,
    bride_mother VARCHAR(255) NULL,
    groom_father VARCHAR(255) NULL,
    groom_mother VARCHAR(255) NULL,
    -- Akad
    akad_datetime DATETIME NULL,
    akad_venue VARCHAR(500) NULL,
    akad_maps_url VARCHAR(500) NULL,
    -- Resepsi
    reception_datetime DATETIME NULL,
    reception_venue VARCHAR(500) NULL,
    reception_maps_url VARCHAR(500) NULL,
    -- Media
    cover_photo_url VARCHAR(500) NULL,
    music_url VARCHAR(500) NULL,
    -- Konten
    love_story TEXT NULL,
    special_message TEXT NULL,
    -- Amplop Digital
    bank_name VARCHAR(100) NULL,
    account_number VARCHAR(50) NULL,
    account_name VARCHAR(255) NULL,
    qris_image_url VARCHAR(500) NULL,
    gopay_number VARCHAR(20) NULL,
    ovo_number VARCHAR(20) NULL,
    dana_number VARCHAR(20) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**invitation_sections**
```sql
CREATE TABLE invitation_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invitation_id BIGINT UNSIGNED NOT NULL,
    template_section_id BIGINT UNSIGNED NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL,
    is_visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE,
    FOREIGN KEY (template_section_id) REFERENCES template_sections(id) ON DELETE CASCADE,
    UNIQUE KEY unique_section (invitation_id, template_section_id),
    INDEX idx_invitation (invitation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**invitation_ornaments**
```sql
CREATE TABLE invitation_ornaments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invitation_id BIGINT UNSIGNED NOT NULL,
    template_ornament_id BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE,
    FOREIGN KEY (template_ornament_id) REFERENCES template_ornaments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_ornament (invitation_id, template_ornament_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**invitation_gallery**
```sql
CREATE TABLE invitation_gallery (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invitation_id BIGINT UNSIGNED NOT NULL,
    url VARCHAR(500) NOT NULL,
    caption VARCHAR(255) NULL,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE,
    INDEX idx_invitation (invitation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**guests**
```sql
CREATE TABLE guests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invitation_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    category VARCHAR(100) NULL,
    unique_code VARCHAR(20) NOT NULL UNIQUE,
    max_pax TINYINT UNSIGNED DEFAULT 1,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE,
    INDEX idx_invitation (invitation_id),
    INDEX idx_unique_code (unique_code),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**rsvps**
```sql
CREATE TABLE rsvps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guest_id BIGINT UNSIGNED NOT NULL UNIQUE,
    attendance ENUM('hadir', 'tidak_hadir', 'belum_konfirmasi') DEFAULT 'belum_konfirmasi',
    pax_count TINYINT UNSIGNED DEFAULT 1,
    message TEXT NULL,
    check_in_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE CASCADE,
    INDEX idx_attendance (attendance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**orders**
```sql
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'paid', 'failed', 'expired') DEFAULT 'pending',
    total_amount DECIMAL(12,2) NOT NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**order_items**
```sql
CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    price_snapshot DECIMAL(12,2) NOT NULL,
    quantity TINYINT UNSIGNED DEFAULT 1,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**payments**
```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    provider ENUM('midtrans') DEFAULT 'midtrans',
    provider_transaction_id VARCHAR(255) NOT NULL UNIQUE,
    payment_method VARCHAR(50) NULL,
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'paid', 'failed', 'refunded', 'expired') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    raw_response JSON NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_provider_tx (provider_transaction_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**user_features**
```sql
CREATE TABLE user_features (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    feature VARCHAR(100) NOT NULL,
    order_item_id BIGINT UNSIGNED NOT NULL,
    metadata JSON NULL,
    activated_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE RESTRICT,
    INDEX idx_user_feature (user_id, feature),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**audit_logs**
```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    subject_type VARCHAR(100) NULL,
    subject_id BIGINT UNSIGNED NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Eloquent Models

#### User Model

```php
class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    
    protected $fillable = [
        'name', 'email', 'password', 'role'
    ];
    
    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    // Relationships
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
    
    public function invitation(): HasOne
    {
        return $this->hasOne(Invitation::class);
    }
    
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    
    public function features(): HasMany
    {
        return $this->hasMany(UserFeature::class);
    }
    
    // Helper methods
    public function hasFeature(string $feature): bool
    {
        return $this->features()
            ->where('feature', $feature)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
```

#### Invitation Model (with Global Scope)

```php
class Invitation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 'template_id', 'subdomain', 'custom_domain',
        'status', 'published_at', 'expires_at', 'view_count'
    ];
    
    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'view_count' => 'integer',
    ];
    
    // Global scope for data isolation
    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
    
    public function content(): HasOne
    {
        return $this->hasOne(InvitationContent::class);
    }
    
    public function sections(): HasMany
    {
        return $this->hasMany(InvitationSection::class);
    }
    
    public function ornaments(): HasMany
    {
        return $this->hasMany(InvitationOrnament::class);
    }
    
    public function gallery(): HasMany
    {
        return $this->hasMany(InvitationGallery::class);
    }
    
    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }
    
    // Scopes
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }
    
    public function scopeDraft(Builder $query): void
    {
        $query->where('status', 'draft');
    }
    
    // Helper methods
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
    
    public function getPublicUrl(): string
    {
        if ($this->custom_domain) {
            return 'https://' . $this->custom_domain;
        }
        
        return 'https://' . $this->subdomain . '.' . config('app.domain');
    }
}
```

#### Template Model

```php
class Template extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'slug', 'name', 'thumbnail_url', 'version',
        'is_free', 'price', 'is_active', 'synced_at'
    ];
    
    protected $casts = [
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'synced_at' => 'datetime',
    ];
    
    // Relationships
    public function sections(): HasMany
    {
        return $this->hasMany(TemplateSection::class);
    }
    
    public function ornaments(): HasMany
    {
        return $this->hasMany(TemplateOrnament::class);
    }
    
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
    
    // Scopes
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
    
    public function scopeFree(Builder $query): void
    {
        $query->where('is_free', true);
    }
    
    // Helper methods
    public function getFolderPath(): string
    {
        return storage_path('templates/' . $this->slug);
    }
    
    public function getUsageCount(): int
    {
        return $this->invitations()->count();
    }
}
```

#### Order Model

```php
class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 'status', 'total_amount', 'paid_at'
    ];
    
    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    
    // Scopes
    public function scopePaid(Builder $query): void
    {
        $query->where('status', 'paid');
    }
    
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }
    
    // Helper methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
    
    public function hasBasePackage(): bool
    {
        return $this->items()
            ->whereHas('product', fn($q) => $q->where('slug', 'base'))
            ->exists();
    }
}
```

#### Guest Model

```php
class Guest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'invitation_id', 'name', 'phone', 'category',
        'unique_code', 'max_pax', 'notes'
    ];
    
    protected $casts = [
        'max_pax' => 'integer',
    ];
    
    // Relationships
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
    
    public function rsvp(): HasOne
    {
        return $this->hasOne(Rsvp::class);
    }
    
    // Helper methods
    public function getPersonalLink(): string
    {
        $invitation = $this->invitation;
        $baseUrl = $invitation->custom_domain 
            ? 'https://' . $invitation->custom_domain
            : 'https://' . $invitation->subdomain . '.' . config('app.domain');
        
        return $baseUrl . '?to=' . $this->unique_code;
    }
    
    public function hasRsvp(): bool
    {
        return $this->rsvp()->exists();
    }
}
```


## Error Handling

### Exception Hierarchy

```php
namespace App\Exceptions;

// Base exception
class UndanganDigitalException extends Exception {}

// Template exceptions
class TemplateException extends UndanganDigitalException {}
class TemplateSyncException extends TemplateException {}
class TemplateNotFoundException extends TemplateException {}
class InvalidTemplateJsonException extends TemplateException {}

// Payment exceptions
class PaymentException extends UndanganDigitalException {}
class MidtransException extends PaymentException {}
class InvalidSignatureException extends PaymentException {}
class PaymentAlreadyProcessedException extends PaymentException {}

// Media exceptions
class MediaException extends UndanganDigitalException {}
class InvalidMimeTypeException extends MediaException {}
class UploadFailedException extends MediaException {}

// Invitation exceptions
class InvitationException extends UndanganDigitalException {}
class SubdomainAlreadyExistsException extends InvitationException {}
class RequiredContentMissingException extends InvitationException {}

// Feature exceptions
class FeatureException extends UndanganDigitalException {}
class FeatureNotActiveException extends FeatureException {}
class FeatureExpiredException extends FeatureException {}
```

### Error Handling Strategy

#### 1. Controller Level

```php
class EditorController extends Controller
{
    public function saveContent(ProfileUpdateRequest $request, InvitationService $service)
    {
        try {
            $invitation = auth()->user()->invitation;
            
            $invitation->content()->updateOrCreate(
                ['invitation_id' => $invitation->id],
                $request->validated()
            );
            
            return back()->with('success', 'Konten berhasil disimpan');
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
            
        } catch (Exception $e) {
            Log::error('Failed to save invitation content', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }
}
```

#### 2. Service Level

```php
class PaymentService
{
    public function processWebhook(array $notification): void
    {
        DB::beginTransaction();
        
        try {
            // Verify signature
            if (!$this->verifySignature($notification, $notification['signature_key'])) {
                throw new InvalidSignatureException('Invalid Midtrans signature');
            }
            
            // Lock payment record
            $payment = Payment::where('provider_transaction_id', $notification['order_id'])
                ->lockForUpdate()
                ->firstOrFail();
            
            // Check idempotency
            if ($payment->status === 'paid') {
                throw new PaymentAlreadyProcessedException('Payment already processed');
            }
            
            // Update payment
            $this->updatePaymentStatus($payment, $notification['transaction_status'], $notification);
            
            // Activate features if paid
            if ($payment->status === 'paid') {
                $this->orderService->activateFeatures($payment->order);
            }
            
            DB::commit();
            
        } catch (PaymentAlreadyProcessedException $e) {
            DB::rollBack();
            Log::info('Duplicate webhook received', ['order_id' => $notification['order_id']]);
            // Don't throw - return success to Midtrans
            
        } catch (InvalidSignatureException $e) {
            DB::rollBack();
            Log::error('Invalid webhook signature', ['notification' => $notification]);
            throw $e;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Webhook processing failed', [
                'notification' => $notification,
                'error' => $e->getMessage()
            ]);
            throw new PaymentException('Failed to process webhook', 0, $e);
        }
    }
}
```

#### 3. Global Exception Handler

```php
class Handler extends ExceptionHandler
{
    protected $dontReport = [
        PaymentAlreadyProcessedException::class,
    ];
    
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
        
        $this->renderable(function (FeatureNotActiveException $e, Request $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Fitur tidak aktif. Silakan upgrade paket Anda.',
                    'upgrade_url' => route('checkout')
                ], 403);
            }
            
            return redirect()->route('checkout')
                ->with('error', 'Fitur tidak aktif. Silakan upgrade paket Anda.');
        });
        
        $this->renderable(function (InvalidMimeTypeException $e, Request $request) {
            return back()->with('error', 'Tipe file tidak didukung. Hanya gambar JPG, PNG, dan WebP yang diperbolehkan.');
        });
    }
}
```

### Validation Rules

#### Form Request Example

```php
class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasFeature('base_package');
    }
    
    public function rules(): array
    {
        return [
            'bride_name' => ['required', 'string', 'max:255'],
            'groom_name' => ['required', 'string', 'max:255'],
            'bride_father' => ['nullable', 'string', 'max:255'],
            'bride_mother' => ['nullable', 'string', 'max:255'],
            'groom_father' => ['nullable', 'string', 'max:255'],
            'groom_mother' => ['nullable', 'string', 'max:255'],
            'akad_datetime' => ['required', 'date', 'after:today'],
            'akad_venue' => ['required', 'string', 'max:500'],
            'akad_maps_url' => ['nullable', 'url', 'max:500'],
            'reception_datetime' => ['required', 'date', 'after_or_equal:akad_datetime'],
            'reception_venue' => ['required', 'string', 'max:500'],
            'reception_maps_url' => ['nullable', 'url', 'max:500'],
            'love_story' => ['nullable', 'string', 'max:5000'],
            'special_message' => ['nullable', 'string', 'max:1000'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'gopay_number' => ['nullable', 'string', 'regex:/^08[0-9]{8,11}$/'],
            'ovo_number' => ['nullable', 'string', 'regex:/^08[0-9]{8,11}$/'],
            'dana_number' => ['nullable', 'string', 'regex:/^08[0-9]{8,11}$/'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'bride_name.required' => 'Nama mempelai wanita wajib diisi',
            'groom_name.required' => 'Nama mempelai pria wajib diisi',
            'akad_datetime.after' => 'Tanggal akad harus setelah hari ini',
            'reception_datetime.after_or_equal' => 'Tanggal resepsi harus setelah atau sama dengan tanggal akad',
            'gopay_number.regex' => 'Format nomor GoPay tidak valid',
        ];
    }
}
```

### Rate Limiting

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        'throttle:api',
        // ...
    ],
];

protected $middlewareAliases = [
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
];

// routes/web.php
Route::post('/rsvp', [PublicRsvpController::class, 'store'])
    ->middleware('throttle:5,1'); // 5 requests per minute

Route::get('/templates/{slug}/preview', [TemplateController::class, 'preview'])
    ->middleware('throttle:60,1'); // 60 requests per minute
```

### Logging Strategy

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'sentry'],
        'ignore_exceptions' => false,
    ],
    
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
    
    'sentry' => [
        'driver' => 'sentry',
        'level' => 'error',
    ],
    
    'payment' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payment.log'),
        'level' => 'info',
        'days' => 90, // Keep payment logs longer
    ],
    
    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'info',
        'days' => 365, // Keep audit logs for 1 year
    ],
];
```

### Health Check Endpoint

```php
Route::get('/health', function () {
    $checks = [
        'database' => false,
        'redis' => false,
        'storage' => false,
    ];
    
    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (Exception $e) {
        Log::error('Health check: Database connection failed', ['error' => $e->getMessage()]);
    }
    
    try {
        Redis::ping();
        $checks['redis'] = true;
    } catch (Exception $e) {
        Log::error('Health check: Redis connection failed', ['error' => $e->getMessage()]);
    }
    
    try {
        Storage::disk('r2')->exists('health-check.txt');
        $checks['storage'] = true;
    } catch (Exception $e) {
        Log::error('Health check: R2 storage connection failed', ['error' => $e->getMessage()]);
    }
    
    $healthy = $checks['database'] && $checks['redis'] && $checks['storage'];
    
    return response()->json([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => now()->toIso8601String(),
    ], $healthy ? 200 : 503);
});
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property-Based Testing Applicability

This application is primarily a CRUD-based web application with UI interactions, infrastructure integrations (payment webhooks, file uploads), and configuration management. **Most requirements are NOT suitable for property-based testing** and should use example-based unit tests and integration tests instead.

**PBT IS appropriate for:**
- Template parsing and rendering (Requirement 2) - pure functions with clear input/output
- Preview data transformation (Requirement 28) - data mapping and validation logic

**PBT is NOT appropriate for:**
- CRUD operations (Requirements 9, 11, 12, 15, 19, 20, 21, 22, 23)
- UI interactions (Requirements 3, 4, 13, 16, 18)
- Infrastructure integrations (Requirements 5, 6, 7, 10, 26, 27)
- Configuration and setup (Requirements 1, 8, 14, 17, 24, 25, 29, 30)

These will use **example-based unit tests** (2-5 examples per feature) and **integration tests** (end-to-end flows).

### Property 1: Template Rendering Round-Trip

*For any* valid template HTML with Mustache syntax and any valid invitation data, rendering the template, then parsing the output, then rendering again SHALL produce output equivalent to the first rendering.

**Validates: Requirements 2.1, 2.2, 2.4, 2.5, 2.6, 2.7, 2.8**

**Test Strategy:**
- Generate random template HTML with various Mustache tags ({{variable}}, {{#section}}, {{^inverted}})
- Generate random invitation data matching template variables
- Verify: `render(parse(render(template, data))) ≡ render(template, data)`
- This validates parsing, rendering, section ordering, ornament positioning, undefined variable handling, and formatting

**Implementation Note:**
```php
// Pest property test
test('template rendering is idempotent (round-trip)', function () {
    $template = generateRandomTemplate(); // Generator creates valid Mustache HTML
    $data = generateRandomInvitationData(); // Generator creates matching data
    
    $firstRender = TemplateService::render($template, $data);
    $parsed = TemplateService::parse($firstRender);
    $secondRender = TemplateService::render($parsed, $data);
    
    expect($secondRender)->toBe($firstRender);
})->repeat(100);
```

### Property 2: HTML Escaping Prevents XSS

*For any* string containing HTML or JavaScript syntax, when rendered in a Mustache template, the output SHALL escape all HTML special characters to prevent XSS attacks.

**Validates: Requirements 2.3**

**Test Strategy:**
- Generate random strings with XSS payloads (<script>, <img onerror>, javascript:, etc.)
- Render in template with Mustache
- Verify all special characters are escaped: `<` → `&lt;`, `>` → `&gt;`, `"` → `&quot;`, `'` → `&#x27;`, `&` → `&amp;`
- Verify no executable code in output

**Implementation Note:**
```php
test('mustache escapes HTML to prevent XSS', function () {
    $xssPayload = generateRandomXssPayload(); // <script>alert('xss')</script>, etc.
    $template = '<div>{{user_input}}</div>';
    $data = ['user_input' => $xssPayload];
    
    $rendered = TemplateService::render($template, $data);
    
    expect($rendered)->not->toContain('<script>');
    expect($rendered)->not->toContain('javascript:');
    expect($rendered)->toContain('&lt;'); // Escaped
})->repeat(100);
```

### Property 3: Preview Data Validation

*For any* data structure, the preview data validator SHALL correctly accept valid structures (containing template_slug, bride_name, groom_name, event_date, preview_at) and reject invalid structures.

**Validates: Requirements 28.2**

**Test Strategy:**
- Generate random JSON structures
- Valid: must have required fields with correct types
- Invalid: missing fields, wrong types, extra fields
- Verify validator returns true for valid, false for invalid

**Implementation Note:**
```php
test('preview data validation accepts valid structures', function () {
    $validData = generateValidPreviewData(); // Has all required fields
    
    expect(PreviewService::validatePreviewData($validData))->toBeTrue();
})->repeat(100);

test('preview data validation rejects invalid structures', function () {
    $invalidData = generateInvalidPreviewData(); // Missing fields or wrong types
    
    expect(PreviewService::validatePreviewData($invalidData))->toBeFalse();
})->repeat(100);
```

### Property 4: Preview Field Mapping Preserves Data

*For any* valid preview data structure, mapping to invitation_contents columns SHALL preserve all field values without loss or corruption.

**Validates: Requirements 28.4**

**Test Strategy:**
- Generate random preview data with all possible fields
- Map to invitation_contents format
- Verify all fields are present and values are unchanged
- Verify field names are correctly mapped (e.g., `event_date` → `akad_datetime`)

**Implementation Note:**
```php
test('preview field mapping preserves all data', function () {
    $previewData = generateRandomPreviewData();
    
    $mapped = PreviewService::mapToInvitationContents($previewData);
    
    // Verify all preview fields are mapped
    expect($mapped)->toHaveKey('bride_name');
    expect($mapped)->toHaveKey('groom_name');
    expect($mapped['bride_name'])->toBe($previewData['bride_name']);
    expect($mapped['groom_name'])->toBe($previewData['groom_name']);
    // ... verify all fields
})->repeat(100);
```

### Property 5: Preview Data Expiration

*For any* preview data with a timestamp, data older than 24 hours SHALL be rejected, and data newer than 24 hours SHALL be accepted.

**Validates: Requirements 28.7**

**Test Strategy:**
- Generate random timestamps (some < 24h ago, some > 24h ago)
- Verify data with `preview_at` > 24h ago returns `isExpired() === true`
- Verify data with `preview_at` < 24h ago returns `isExpired() === false`
- Test boundary: exactly 24h ago

**Implementation Note:**
```php
test('preview data older than 24 hours is expired', function () {
    $timestamp = now()->subHours(rand(25, 100)); // 25-100 hours ago
    $previewData = ['preview_at' => $timestamp->toIso8601String()];
    
    expect(PreviewService::isPreviewDataExpired($previewData))->toBeTrue();
})->repeat(100);

test('preview data newer than 24 hours is not expired', function () {
    $timestamp = now()->subHours(rand(0, 23)); // 0-23 hours ago
    $previewData = ['preview_at' => $timestamp->toIso8601String()];
    
    expect(PreviewService::isPreviewDataExpired($previewData))->toBeFalse();
})->repeat(100);
```

### Non-Property Requirements

The remaining requirements (1, 3-27, 29-30) will be tested with:

**Example-Based Unit Tests:**
- Template sync (Requirement 1): 3-5 examples with valid/invalid template.json
- Guest management (Requirement 15): Add, edit, delete, import CSV
- RSVP submission (Requirement 17): Valid submission, invalid guest_id, rate limiting
- Section reordering (Requirement 11): Reorder 3 sections, verify sort_order
- Ornament toggling (Requirement 12): Toggle on/off, verify is_active

**Integration Tests:**
- Payment flow (Requirements 5, 6, 7): Checkout → Midtrans → Webhook → Feature activation
- Media upload (Requirement 10): Upload image → R2 → Optimize → Signed URL
- Invitation publishing (Requirement 13): Validate → Publish → Public access
- Custom domain (Requirement 20): Set domain → DNS check → Resolution

**Smoke Tests:**
- Health check (Requirement 27): Database, Redis, R2 connectivity
- Template sync command (Requirement 1): Run once, verify templates in DB
- Admin panel access (Requirements 21, 22, 23): Login as admin, view pages


## Testing Strategy

### Overview

This application uses a **dual testing approach**:
1. **Property-Based Tests** (Pest + fast-check or similar): For pure functions with universal properties (5 properties, 100 iterations each)
2. **Example-Based Unit Tests** (Pest): For specific behaviors, edge cases, and business logic (2-5 examples per feature)
3. **Integration Tests** (Pest): For end-to-end flows involving multiple components
4. **Browser Tests** (Pest + Laravel Dusk): For critical user journeys

### Test Organization

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── TemplateServiceTest.php
│   │   ├── OrderServiceTest.php
│   │   ├── PaymentServiceTest.php
│   │   ├── PreviewServiceTest.php
│   │   └── MediaServiceTest.php
│   ├── Models/
│   │   ├── InvitationTest.php
│   │   ├── UserTest.php
│   │   └── GuestTest.php
│   └── Helpers/
│       └── SubdomainGeneratorTest.php
├── Feature/
│   ├── Auth/
│   │   ├── RegistrationTest.php
│   │   └── LoginTest.php
│   ├── Dashboard/
│   │   ├── ContentEditorTest.php
│   │   ├── GuestManagementTest.php
│   │   ├── RsvpDashboardTest.php
│   │   └── SectionManagementTest.php
│   ├── Public/
│   │   ├── TemplatePreviewTest.php
│   │   ├── CheckoutTest.php
│   │   └── InvitationViewTest.php
│   ├── Payment/
│   │   ├── MidtransWebhookTest.php
│   │   └── FeatureActivationTest.php
│   └── Admin/
│       ├── ProductManagementTest.php
│       └── TemplateManagementTest.php
├── Property/
│   ├── TemplateRenderingTest.php      # Property 1: Round-trip
│   ├── XssEscapingTest.php            # Property 2: HTML escaping
│   ├── PreviewValidationTest.php      # Property 3: Data validation
│   ├── PreviewMappingTest.php         # Property 4: Field mapping
│   └── PreviewExpirationTest.php      # Property 5: Timestamp logic
└── Browser/
    ├── PreviewFlowTest.php
    ├── CheckoutFlowTest.php
    └── DashboardFlowTest.php
```

### Property-Based Testing Configuration

**Library**: Use `pestphp/pest` with custom generators or integrate `azjezz/pest-plugin-faker` for property-based testing.

**Configuration**:
```php
// tests/Pest.php
uses(Tests\TestCase::class)->in('Feature', 'Property');

// Property test configuration
function propertyTest(string $description, Closure $test): void
{
    test($description, function () use ($test) {
        // Run test 100 times with different random inputs
        for ($i = 0; $i < 100; $i++) {
            $test();
        }
    })->group('property');
}
```

**Tagging Convention**:
```php
// tests/Property/TemplateRenderingTest.php
test('template rendering round-trip preserves output', function () {
    // Feature: undangan-digital, Property 1: Template Rendering Round-Trip
    $template = generateRandomTemplate();
    $data = generateRandomInvitationData();
    
    $firstRender = app(TemplateService::class)->renderInvitation($template, $data);
    $parsed = app(TemplateService::class)->parseRenderedHtml($firstRender);
    $secondRender = app(TemplateService::class)->renderInvitation($parsed, $data);
    
    expect($secondRender)->toBe($firstRender);
})->repeat(100)->group('property', 'template');
```

### Example-Based Unit Tests

**Template Sync (Requirement 1)**:
```php
test('sync command registers valid template', function () {
    // Arrange: Create template folder with valid template.json
    $this->createTemplateFolder('romantic', [
        'name' => 'Romantic',
        'sections' => ['hero', 'story'],
        'ornaments' => [['file' => 'flower.html', 'position' => 'top']]
    ]);
    
    // Act
    $this->artisan('templates:sync')->assertSuccessful();
    
    // Assert
    $this->assertDatabaseHas('templates', ['slug' => 'romantic', 'name' => 'Romantic']);
    $this->assertDatabaseCount('template_sections', 2);
    $this->assertDatabaseCount('template_ornaments', 1);
});

test('sync command skips invalid template.json', function () {
    $this->createTemplateFolder('invalid', ['invalid' => 'json']);
    
    $this->artisan('templates:sync')->assertSuccessful();
    
    $this->assertDatabaseMissing('templates', ['slug' => 'invalid']);
});
```

**Guest Management (Requirement 15)**:
```php
test('user can add guest manually', function () {
    $user = User::factory()->withBasePackage()->create();
    $invitation = Invitation::factory()->for($user)->create();
    
    $this->actingAs($user)
        ->post(route('dashboard.guests.store'), [
            'name' => 'John Doe',
            'phone' => '+628123456789',
            'category' => 'keluarga',
            'max_pax' => 2
        ])
        ->assertRedirect()
        ->assertSessionHas('success');
    
    $this->assertDatabaseHas('guests', [
        'invitation_id' => $invitation->id,
        'name' => 'John Doe',
        'max_pax' => 2
    ]);
    
    $guest = Guest::where('name', 'John Doe')->first();
    expect($guest->unique_code)->toHaveLength(20);
});

test('user can import guests from CSV', function () {
    $user = User::factory()->withBasePackage()->create();
    $invitation = Invitation::factory()->for($user)->create();
    
    $csv = UploadedFile::fake()->createWithContent('guests.csv', 
        "name,phone,category,max_pax\n" .
        "John Doe,08123456789,keluarga,2\n" .
        "Jane Smith,08987654321,teman,1"
    );
    
    $this->actingAs($user)
        ->post(route('dashboard.guests.import'), ['file' => $csv])
        ->assertRedirect()
        ->assertSessionHas('success');
    
    $this->assertDatabaseCount('guests', 2);
});
```

**RSVP Submission (Requirement 17)**:
```php
test('guest can submit RSVP', function () {
    $guest = Guest::factory()->create();
    
    $this->post(route('rsvp.store'), [
        'guest_id' => $guest->id,
        'attendance' => 'hadir',
        'pax_count' => 2,
        'message' => 'Terima kasih atas undangannya'
    ])
    ->assertRedirect()
    ->assertSessionHas('success');
    
    $this->assertDatabaseHas('rsvps', [
        'guest_id' => $guest->id,
        'attendance' => 'hadir',
        'pax_count' => 2
    ]);
});

test('RSVP endpoint is rate limited', function () {
    $guest = Guest::factory()->create();
    
    // Make 5 requests (should succeed)
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('rsvp.store'), [
            'guest_id' => $guest->id,
            'attendance' => 'hadir',
            'pax_count' => 1
        ])->assertSuccessful();
    }
    
    // 6th request should be rate limited
    $this->post(route('rsvp.store'), [
        'guest_id' => $guest->id,
        'attendance' => 'hadir',
        'pax_count' => 1
    ])->assertStatus(429);
});
```

### Integration Tests

**Payment Flow (Requirements 5, 6, 7)**:
```php
test('complete payment flow activates features', function () {
    Queue::fake();
    
    // 1. User creates order
    $user = User::factory()->create();
    $template = Template::factory()->create();
    $basePackage = Product::factory()->basePackage()->create();
    
    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'template_id' => $template->id,
            'items' => [
                ['product_id' => $basePackage->id, 'quantity' => 1]
            ]
        ])
        ->assertRedirect();
    
    $order = Order::where('user_id', $user->id)->first();
    expect($order->status)->toBe('pending');
    
    // 2. Simulate Midtrans webhook
    $notification = [
        'order_id' => $order->id,
        'transaction_status' => 'settlement',
        'gross_amount' => $order->total_amount,
        'signature_key' => 'valid_signature'
    ];
    
    $this->post(route('webhook.midtrans'), $notification)
        ->assertOk();
    
    // 3. Verify payment updated
    $order->refresh();
    expect($order->status)->toBe('paid');
    expect($order->paid_at)->not->toBeNull();
    
    // 4. Verify features activated
    expect($user->hasFeature('base_package'))->toBeTrue();
    
    // 5. Verify invitation created
    $invitation = $user->invitation;
    expect($invitation)->not->toBeNull();
    expect($invitation->template_id)->toBe($template->id);
    expect($invitation->subdomain)->not->toBeNull();
    
    // 6. Verify email queued
    Queue::assertPushed(SendEmailConfirmation::class);
});
```

**Media Upload (Requirement 10)**:
```php
test('image upload flow', function () {
    Storage::fake('r2');
    Queue::fake();
    
    $user = User::factory()->withBasePackage()->create();
    $invitation = Invitation::factory()->for($user)->create();
    
    $image = UploadedFile::fake()->image('cover.jpg', 1920, 1080);
    
    $this->actingAs($user)
        ->post(route('dashboard.media.upload'), [
            'file' => $image,
            'type' => 'cover_photo'
        ])
        ->assertOk()
        ->assertJsonStructure(['url']);
    
    // Verify file uploaded to R2
    Storage::disk('r2')->assertExists(
        $user->id . '/' . $image->hashName()
    );
    
    // Verify optimization job queued
    Queue::assertPushed(OptimizeUploadedImage::class);
});

test('invalid MIME type is rejected', function () {
    $user = User::factory()->withBasePackage()->create();
    
    $file = UploadedFile::fake()->create('malicious.php', 100);
    
    $this->actingAs($user)
        ->post(route('dashboard.media.upload'), ['file' => $file])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});
```

**Invitation Publishing (Requirement 13)**:
```php
test('publish invitation flow', function () {
    $user = User::factory()->withBasePackage()->create();
    $invitation = Invitation::factory()->for($user)->draft()->create();
    $invitation->content()->create([
        'bride_name' => 'Justine',
        'groom_name' => 'Emma',
        'akad_datetime' => now()->addMonth(),
        'akad_venue' => 'Grand Ballroom',
        'reception_datetime' => now()->addMonth(),
        'reception_venue' => 'Grand Ballroom'
    ]);
    
    $this->actingAs($user)
        ->post(route('dashboard.invitation.publish'))
        ->assertRedirect()
        ->assertSessionHas('success');
    
    $invitation->refresh();
    expect($invitation->status)->toBe('published');
    expect($invitation->published_at)->not->toBeNull();
    
    // Verify public access works
    $this->get('https://' . $invitation->subdomain . '.undangan.com')
        ->assertOk()
        ->assertSee('Justine')
        ->assertSee('Emma');
});
```

### Browser Tests (Critical User Journeys)

**Preview Flow**:
```php
test('user can preview template with own data', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/templates')
            ->click('@template-romantic')
            ->waitForText('Romantic')
            ->click('@try-with-your-data')
            ->type('bride_name', 'Justine')
            ->type('groom_name', 'Emma')
            ->type('event_date', '2025-06-14')
            ->waitForText('Justine')
            ->waitForText('Emma')
            ->click('@buy-now')
            ->assertPathIs('/checkout')
            ->assertSee('Justine')
            ->assertSee('Emma');
    });
});
```

**Checkout Flow**:
```php
test('complete checkout flow', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/checkout?template=romantic')
            ->assertSee('Paket Undangan Seumur Hidup')
            ->check('@addon-custom-domain')
            ->assertSee('Total')
            ->click('@submit-checkout')
            ->waitForText('Midtrans')
            ->assertSee('QRIS');
    });
});
```

### Test Coverage Goals

- **Unit Tests**: 80%+ coverage for Services and Models
- **Feature Tests**: 100% coverage for all HTTP endpoints
- **Property Tests**: 5 properties, 100 iterations each (500 total test runs)
- **Integration Tests**: All critical flows (payment, upload, publishing)
- **Browser Tests**: 3-5 critical user journeys

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific groups
php artisan test --group=property
php artisan test --group=integration
php artisan test --group=browser

# Run with coverage
php artisan test --coverage --min=80

# Run property tests only
php artisan test tests/Property

# Run specific test file
php artisan test tests/Feature/Dashboard/ContentEditorTest.php

# Run with filter
php artisan test --filter=payment
```

### Continuous Integration

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: testing
          MYSQL_ROOT_PASSWORD: password
      redis:
        image: redis:7
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, mysql, redis
      
      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist
      
      - name: Run tests
        run: php artisan test --parallel --coverage --min=80
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_DATABASE: testing
          REDIS_HOST: 127.0.0.1
```

---

## Document Status

**Version:** 1.0  
**Created:** 2025-01-09  
**Status:** Ready for Review

This design document provides comprehensive technical specifications for the Undangan Digital application, covering:
- ✅ System architecture with request flow diagrams
- ✅ Complete component interfaces (Services, Middleware, Jobs, Models)
- ✅ Full database schema with 15 tables and relationships
- ✅ Error handling strategy with exception hierarchy
- ✅ 5 correctness properties for property-based testing
- ✅ Comprehensive testing strategy (unit, integration, property, browser tests)

**Next Steps:**
1. Review design with stakeholders
2. Create tasks.md based on this design
3. Begin implementation starting with database migrations
4. Set up testing infrastructure (Pest + property test generators)

