# Undangan Digital — Plan Terbaru

## Keputusan Bisnis yang Sudah Disepakati

- **Target user:** Pengantin langsung (B2C), beli sendiri, self-service + bisa minta bantuan
- **Model harga:** Lifetime per undangan, bukan subscription bulanan
- **Add-on:** Custom domain, managed setup, extra storage — dibeli terpisah via cart
- **Hosting:** Semua di server yang sama, isolasi data by `user_id` (bukan multi-tenant DB)
- **Template:** Folder-based, didaftarkan ke DB via artisan command, tidak ada yang hardcode
- **Preview:** Dummy dulu → bisa isi data sendiri → checkout → data ter-restore otomatis
- **Tidak ada stancl/tenancy** — over-engineering untuk kasus ini

---

## Tech Stack

| Layer | Pilihan | Alasan |
|---|---|---|
| Backend | Laravel 13 | Ekosistem luas, queue, storage, routing sudah lengkap |
| Frontend app | Vue 3 + InertiaJS | SPA feel tanpa API terpisah, dashboard pengantin |
| Template render | HTML + CSS + JS + Mustache.js | Ringan, tidak perlu compile, bisa tambah template tanpa deploy |
| Styling app | TailwindCSS + shadcn-vue | Cepat, konsisten |
| Database | MySQL 8 | Relasional, mature |
| Cache & queue | Redis 7 + Laravel Horizon | Queue monitoring, cache session |
| Storage | Cloudflare R2 | S3-compatible, biaya murah, CDN built-in |
| Web server | Nginx | Wildcard subdomain + SSL |
| Error tracking | Sentry | Wajib sejak hari pertama |
| Payment | Midtrans | Gateway Indonesia, support QRIS, VA, e-wallet |

---

## Arsitektur Sistem

```
Browser tamu / pengantin
        │
      Nginx  (wildcard SSL *.undangan.com)
        │
        ├── justinemma.undangan.com      → resolve by subdomain
        ├── undangan.namadomain.com      → resolve by custom_domain field
        └── undangan.com                 → landing page + preview + checkout
                │
           Laravel App
                │
        ├── MySQL (satu DB, semua user, isolasi by user_id)
        ├── Redis (session, queue, cache)
        └── Cloudflare R2 (foto, lagu, video per user)
```

**Cara resolve undangan:** Middleware baca `$request->getHost()`, cari di tabel `invitations` by `subdomain` atau `custom_domain`, lalu set invitation aktif ke request context. Semua query otomatis scope ke invitation tersebut.

---

## Template System

### Struktur Folder

```
storage/templates/
└── romantic/
    ├── template.json          ← metadata wajib
    ├── sections/
    │   ├── hero.html          ← pakai {{variable}} Mustache
    │   ├── countdown.html
    │   ├── gallery.html
    │   ├── story.html
    │   ├── gift.html
    │   └── rsvp.html
    ├── ornaments/
    │   ├── flower-top.html    ← dekoratif, posisi: top/bottom/overlay
    │   ├── flower-bottom.html
    │   └── divider.html
    └── assets/
        ├── style.css
        └── script.js          ← boleh pakai vanilla JS, bukan framework
```

### template.json

```json
{
  "name": "Romantic",
  "slug": "romantic",
  "version": "1.0.0",
  "thumbnail": "thumbnail.jpg",
  "is_free": false,
  "price": 99000,
  "sections": ["hero", "countdown", "story", "gallery", "gift", "rsvp"],
  "ornaments": [
    { "file": "flower-top.html",    "position": "top",     "default_active": true },
    { "file": "flower-bottom.html", "position": "bottom",  "default_active": true },
    { "file": "divider.html",       "position": "between", "default_active": false }
  ],
  "variables": [
    "bride_name", "groom_name", "event_date", "akad_time",
    "reception_time", "venue_name", "venue_address", "maps_url",
    "cover_photo_url", "gallery_urls", "music_url", "love_story",
    "bride_father", "bride_mother", "groom_father", "groom_mother"
  ]
}
```

### Artisan Command untuk Sync

```bash
php artisan templates:sync
# Scan semua folder di storage/templates/
# Baca template.json masing-masing
# Upsert ke tabel templates, template_sections, template_ornaments
# Tidak menghapus template yang sudah ada, hanya update
```

---

## Database Schema

### Prinsip

- Tidak ada `settings_json` junk drawer — setiap data punya kolom sendiri
- Semua harga disimpan di DB, tidak ada yang hardcode di kode
- Isolasi data by `user_id` atau `invitation_id`, bukan by database
- Audit log untuk aksi kritis

---

### Tabel: `users`

```sql
id                  BIGINT PK AUTO_INCREMENT
name                VARCHAR(255)
email               VARCHAR(255) UNIQUE
password            VARCHAR(255)
role                ENUM('admin', 'user') DEFAULT 'user'
email_verified_at   TIMESTAMP NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

---

### Tabel: `products`

Semua yang bisa dibeli — paket dasar maupun add-on. Harga diatur dari admin panel.

```sql
id              BIGINT PK AUTO_INCREMENT
type            ENUM('base_package', 'addon')
slug            VARCHAR(100) UNIQUE        -- 'base', 'custom_domain', 'managed_setup', 'extra_storage'
name            VARCHAR(255)               -- nama tampil ke user
description     TEXT NULL
price           DECIMAL(12,2)              -- bisa diubah kapan saja dari admin
is_active       BOOLEAN DEFAULT TRUE
metadata        JSON NULL                  -- config tambahan per product type
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

Contoh data awal:

| slug | name | price |
|---|---|---|
| base | Paket Undangan Seumur Hidup | 99000 |
| custom_domain | Custom Domain | 49000 |
| managed_setup | Dibantu Setup oleh Tim | 79000 |
| extra_storage | Tambah Storage 1GB | 29000 |

---

### Tabel: `templates`

Didaftarkan via `php artisan templates:sync`, tidak pernah diisi manual.

```sql
id              BIGINT PK AUTO_INCREMENT
slug            VARCHAR(100) UNIQUE        -- nama folder
name            VARCHAR(255)
thumbnail_url   VARCHAR(500) NULL
version         VARCHAR(20) DEFAULT '1.0.0'
is_free         BOOLEAN DEFAULT FALSE
price           DECIMAL(12,2) DEFAULT 0    -- 0 jika is_free = true
is_active       BOOLEAN DEFAULT TRUE       -- bisa nonaktifkan dari admin tanpa hapus folder
synced_at       TIMESTAMP                  -- kapan terakhir di-sync
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

---

### Tabel: `template_sections`

```sql
id              BIGINT PK AUTO_INCREMENT
template_id     BIGINT FK → templates.id
file            VARCHAR(100)               -- 'hero.html', 'gallery.html', dst
label           VARCHAR(100)               -- nama ramah: 'Hero', 'Galeri', dst
sort_order      TINYINT                    -- urutan default dari template.json
is_required     BOOLEAN DEFAULT FALSE      -- section wajib, tidak bisa dihapus user
created_at      TIMESTAMP
```

---

### Tabel: `template_ornaments`

```sql
id              BIGINT PK AUTO_INCREMENT
template_id     BIGINT FK → templates.id
file            VARCHAR(100)               -- 'flower-top.html'
label           VARCHAR(100)
position        ENUM('top','bottom','between','overlay')
default_active  BOOLEAN DEFAULT TRUE
created_at      TIMESTAMP
```

---

### Tabel: `invitations`

Satu user = satu undangan (MVP). Bisa dikembangkan jadi lebih dari satu nanti.

```sql
id              BIGINT PK AUTO_INCREMENT
user_id         BIGINT FK → users.id
template_id     BIGINT FK → templates.id
subdomain       VARCHAR(100) UNIQUE NULL   -- 'justinemma' → justinemma.undangan.com
custom_domain   VARCHAR(255) UNIQUE NULL   -- 'undangan.namamereka.com' (jika beli addon)
status          ENUM('draft','published','archived') DEFAULT 'draft'
published_at    TIMESTAMP NULL
expires_at      TIMESTAMP NULL             -- opsional, jika mau ada masa aktif
view_count      INT DEFAULT 0
created_at      TIMESTAMP
updated_at      TIMESTAMP

INDEX idx_subdomain (subdomain)
INDEX idx_custom_domain (custom_domain)
INDEX idx_user (user_id)
```

---

### Tabel: `invitation_contents`

Data pengantin yang di-inject ke template via Mustache.js.

```sql
id                  BIGINT PK AUTO_INCREMENT
invitation_id       BIGINT FK → invitations.id UNIQUE
-- Identitas
bride_name          VARCHAR(255) NULL
groom_name          VARCHAR(255) NULL
bride_father        VARCHAR(255) NULL
bride_mother        VARCHAR(255) NULL
groom_father        VARCHAR(255) NULL
groom_mother        VARCHAR(255) NULL
-- Akad
akad_datetime       DATETIME NULL
akad_venue          VARCHAR(500) NULL
akad_maps_url       VARCHAR(500) NULL
-- Resepsi
reception_datetime  DATETIME NULL
reception_venue     VARCHAR(500) NULL
reception_maps_url  VARCHAR(500) NULL
-- Media
cover_photo_url     VARCHAR(500) NULL
music_url           VARCHAR(500) NULL
-- Konten bebas
love_story          TEXT NULL
special_message     TEXT NULL
-- Amplop digital
bank_name           VARCHAR(100) NULL
account_number      VARCHAR(50) NULL
account_name        VARCHAR(255) NULL
qris_image_url      VARCHAR(500) NULL
gopay_number        VARCHAR(20) NULL
ovo_number          VARCHAR(20) NULL
dana_number         VARCHAR(20) NULL
-- Metadata
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

---

### Tabel: `invitation_sections`

Urutan dan visibilitas section per undangan. User bisa reorder dari dashboard.

```sql
id                      BIGINT PK AUTO_INCREMENT
invitation_id           BIGINT FK → invitations.id
template_section_id     BIGINT FK → template_sections.id
sort_order              TINYINT                -- urutan yang sudah dikustomisasi user
is_visible              BOOLEAN DEFAULT TRUE   -- bisa disembunyikan tanpa dihapus
created_at              TIMESTAMP

UNIQUE KEY unique_section (invitation_id, template_section_id)
INDEX idx_invitation (invitation_id)
```

---

### Tabel: `invitation_ornaments`

Ornamen mana yang aktif per undangan.

```sql
id                          BIGINT PK AUTO_INCREMENT
invitation_id               BIGINT FK → invitations.id
template_ornament_id        BIGINT FK → template_ornaments.id
is_active                   BOOLEAN DEFAULT TRUE

UNIQUE KEY unique_ornament (invitation_id, template_ornament_id)
```

---

### Tabel: `invitation_gallery`

```sql
id              BIGINT PK AUTO_INCREMENT
invitation_id   BIGINT FK → invitations.id
url             VARCHAR(500)
caption         VARCHAR(255) NULL
sort_order      TINYINT DEFAULT 0
created_at      TIMESTAMP

INDEX idx_invitation (invitation_id)
```

---

### Tabel: `guests`

```sql
id              BIGINT PK AUTO_INCREMENT
invitation_id   BIGINT FK → invitations.id
name            VARCHAR(255)
phone           VARCHAR(20) NULL           -- format E.164: +628xxxxxxxxxx
category        VARCHAR(100) NULL          -- 'keluarga', 'teman', 'rekan'
unique_code     VARCHAR(20) UNIQUE         -- untuk personal link & QR
max_pax         TINYINT DEFAULT 1
notes           TEXT NULL
created_at      TIMESTAMP

INDEX idx_invitation (invitation_id)
INDEX idx_unique_code (unique_code)
```

Link tamu: `justinemma.undangan.com?to=abc123`
Nama tamu muncul otomatis di halaman: "Kepada Yth. Budi Santoso"

---

### Tabel: `rsvps`

```sql
id              BIGINT PK AUTO_INCREMENT
guest_id        BIGINT FK → guests.id UNIQUE
attendance      ENUM('hadir','tidak_hadir','belum_konfirmasi') DEFAULT 'belum_konfirmasi'
pax_count       TINYINT DEFAULT 1
message         TEXT NULL
check_in_at     TIMESTAMP NULL
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

---

### Tabel: `orders`

Satu order bisa berisi paket dasar + beberapa add-on sekaligus.

```sql
id              BIGINT PK AUTO_INCREMENT
user_id         BIGINT FK → users.id
status          ENUM('pending','paid','failed','expired') DEFAULT 'pending'
total_amount    DECIMAL(12,2)
paid_at         TIMESTAMP NULL
created_at      TIMESTAMP
updated_at      TIMESTAMP

INDEX idx_user (user_id)
```

---

### Tabel: `order_items`

```sql
id              BIGINT PK AUTO_INCREMENT
order_id        BIGINT FK → orders.id
product_id      BIGINT FK → products.id
price_snapshot  DECIMAL(12,2)              -- harga saat transaksi, tidak berubah meski harga product diubah nanti
quantity        TINYINT DEFAULT 1
metadata        JSON NULL                  -- misal: { "domain": "undangan.namamereka.com" }
created_at      TIMESTAMP
```

---

### Tabel: `payments`

```sql
id                      BIGINT PK AUTO_INCREMENT
order_id                BIGINT FK → orders.id
provider                ENUM('midtrans') DEFAULT 'midtrans'
provider_transaction_id VARCHAR(255) UNIQUE
payment_method          VARCHAR(50) NULL   -- 'gopay', 'bca_va', 'qris', dst
amount                  DECIMAL(12,2)
status                  ENUM('pending','paid','failed','refunded','expired') DEFAULT 'pending'
paid_at                 TIMESTAMP NULL
raw_response            JSON NULL          -- response mentah dari Midtrans
created_at              TIMESTAMP

INDEX idx_provider_tx (provider_transaction_id)
```

---

### Tabel: `user_features`

Fitur apa yang sudah aktif per user berdasarkan pembelian. Di-update otomatis setelah payment sukses.

```sql
id              BIGINT PK AUTO_INCREMENT
user_id         BIGINT FK → users.id
feature         VARCHAR(100)               -- 'base_package', 'custom_domain', 'managed_setup', 'extra_storage'
order_item_id   BIGINT FK → order_items.id -- traceable ke transaksi asalnya
metadata        JSON NULL                  -- misal: { "domain": "undangan.namamereka.com", "storage_gb": 1 }
activated_at    TIMESTAMP
expires_at      TIMESTAMP NULL             -- null = lifetime

INDEX idx_user_feature (user_id, feature)
```

Cara cek akses fitur di mana saja:

```php
// Helper sederhana, tidak perlu package apapun
function userHasFeature(int $userId, string $feature): bool {
    return UserFeature::where('user_id', $userId)
        ->where('feature', $feature)
        ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
        ->exists();
}
```

---

### Tabel: `audit_logs`

```sql
id              BIGINT PK AUTO_INCREMENT
user_id         BIGINT NULL
action          VARCHAR(100)               -- 'invitation.published', 'order.paid', dst
subject_type    VARCHAR(100) NULL
subject_id      BIGINT NULL
metadata        JSON NULL
ip_address      VARCHAR(45) NULL
created_at      TIMESTAMP

INDEX idx_user_action (user_id, action)
INDEX idx_created (created_at)
```

---

## Preview System (Tanpa Login)

### Flow

```
1. User buka /templates → pilih template → lihat dengan data dummy
2. User klik "Coba dengan datamu" → muncul form mini (nama, tanggal)
3. Preview update realtime via Mustache.js di browser
4. Foto preview: URL.createObjectURL() — tidak di-upload ke server
5. User klik "Beli sekarang" → data disimpan ke sessionStorage
6. Redirect ke /checkout?template=romantic
7. Halaman checkout baca sessionStorage → tampilkan ringkasan
8. User login atau daftar (jika belum punya akun)
9. Setelah bayar → controller terima data preview dari JS → simpan ke DB
10. User langsung masuk dashboard dengan data sudah terisi
```

### Data di sessionStorage

```json
{
  "template_slug": "romantic",
  "bride_name": "Justine",
  "groom_name": "Emma",
  "event_date": "2025-06-14",
  "preview_at": "2025-05-09T10:30:00Z"
}
```

Foto tidak disimpan di sessionStorage — hanya di-upload setelah user punya akun.

### Edge Case: Beda Device

Jika user preview di HP lalu bayar di laptop, sessionStorage tidak ikut. Solusi: halaman checkout tampilkan form isian ulang yang sudah pre-filled dari sessionStorage jika ada, kosong jika tidak ada. Tidak perlu solusi teknis kompleks.

---

## Routing

```php
// Landing page & preview (public, tanpa login)
Route::get('/', [LandingController::class, 'index']);
Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/templates/{slug}/preview', [TemplateController::class, 'preview']);
Route::get('/checkout', [CheckoutController::class, 'index']);
Route::post('/checkout', [CheckoutController::class, 'store']);

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Dashboard pengantin (harus login + punya akun aktif)
Route::middleware(['auth', 'has_base_package'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/editor', [EditorController::class, 'index']);
    Route::post('/editor/content', [EditorController::class, 'saveContent']);
    Route::post('/editor/sections/reorder', [EditorController::class, 'reorderSections']);
    Route::post('/editor/ornaments', [EditorController::class, 'toggleOrnament']);
    Route::post('/media/upload', [MediaController::class, 'upload']);
    Route::get('/guests', [GuestController::class, 'index']);
    Route::post('/guests/import', [GuestController::class, 'import']);
    Route::get('/rsvp', [RsvpController::class, 'index']);
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings/domain', [SettingsController::class, 'setDomain']);
    Route::post('/invitation/publish', [InvitationController::class, 'publish']);
});

// Public undangan (resolve by subdomain / custom domain)
// Ditangani Nginx → middleware ResolveInvitation
Route::middleware('resolve_invitation')->group(function () {
    Route::get('/', [PublicInvitationController::class, 'show']);
    Route::post('/rsvp', [PublicRsvpController::class, 'store']);
});

// Admin panel (FilamentPHP, route otomatis)
// Akses: /admin

// Webhook
Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle']);
```

---

## Middleware Penting

### ResolveInvitation

```php
class ResolveInvitation {
    public function handle(Request $request, Closure $next) {
        $host = $request->getHost();
        $platformDomain = config('app.domain'); // 'undangan.com'

        // Cek apakah subdomain platform
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

        $request->attributes->set('invitation', $invitation);
        return $next($request);
    }
}
```

### HasBasePackage

```php
class HasBasePackage {
    public function handle(Request $request, Closure $next) {
        abort_unless(
            userHasFeature(auth()->id(), 'base_package'),
            403,
            'Silakan beli paket terlebih dahulu.'
        );
        return $next($request);
    }
}
```

---

## Payment Flow

```
1. User klik "Beli" di halaman checkout
2. Controller buat order + order_items di DB (status: pending)
3. Request ke Midtrans API → dapat snap_token
4. Redirect ke Midtrans Snap atau embed di halaman
5. User bayar
6. Midtrans kirim webhook ke /webhook/midtrans
7. Controller verifikasi signature → update payment status
8. Jika paid: aktifkan fitur di user_features, update order status
9. Kirim email konfirmasi via queue
10. Jika user sudah login: redirect ke dashboard
    Jika belum login: redirect ke /login?next=dashboard
```

### Idempotency Webhook

```php
// Selalu cek dulu sebelum proses
$payment = Payment::where('provider_transaction_id', $request->order_id)
    ->lockForUpdate()
    ->firstOrFail();

if ($payment->status === 'paid') {
    return response()->json(['status' => 'already_processed']);
}
```

---

## Security

- **Isolasi data:** Semua query ke `invitations`, `guests`, `rsvps` harus include `where('user_id', auth()->id())` — gunakan global scope di model
- **Upload file:** Validasi MIME dengan `finfo`, bukan dari ekstensi. Simpan dengan nama UUID, bukan nama asli user
- **Rate limiting:** RSVP endpoint 5x/menit per IP, preview 60x/menit per IP
- **Signed URL:** Asset di R2 pakai temporary URL (expire 1 jam), bukan URL publik permanen
- **Webhook:** Verifikasi signature Midtrans sebelum proses apapun
- **XSS:** Semua input user di-escape sebelum di-render. Mustache.js by default escape HTML
- **CSRF:** Laravel CSRF aktif untuk semua POST route kecuali webhook (di-exclude di VerifyCsrfToken)

---

## Folder Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Dashboard/
│   │   └── Public/
│   ├── Middleware/
│   │   ├── ResolveInvitation.php
│   │   └── HasBasePackage.php
│   └── Requests/
├── Models/
│   ├── User.php
│   ├── Invitation.php
│   ├── InvitationContent.php
│   ├── InvitationSection.php
│   ├── InvitationOrnament.php
│   ├── InvitationGallery.php
│   ├── Template.php
│   ├── TemplateSection.php
│   ├── TemplateOrnament.php
│   ├── Product.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Payment.php
│   ├── UserFeature.php
│   ├── Guest.php
│   ├── Rsvp.php
│   └── AuditLog.php
├── Services/
│   ├── TemplateService.php        -- sync folder, render undangan
│   ├── OrderService.php           -- buat order, aktifkan fitur
│   ├── PaymentService.php         -- integrasi Midtrans
│   ├── PreviewService.php         -- inject data ke template untuk preview
│   └── MediaService.php           -- upload, resize, simpan ke R2
├── Jobs/
│   ├── SendEmailConfirmation.php
│   └── OptimizeUploadedImage.php
└── Console/Commands/
    └── SyncTemplates.php          -- php artisan templates:sync

resources/
├── js/
│   ├── Pages/
│   │   ├── Landing/
│   │   ├── Templates/             -- halaman pilih template + preview
│   │   ├── Checkout/
│   │   ├── Dashboard/
│   │   └── Auth/
│   └── Components/
└── views/
    └── invitation.blade.php       -- wrapper render template HTML

storage/
└── templates/
    ├── romantic/
    ├── elegant/
    └── modern/
```

---

## MVP Priority

### Phase 1 — Bisa Jual (4 minggu)

**Week 1**
- Setup Laravel + Vue + Inertia + Tailwind
- Auth (register, login, email verification)
- Artisan command `templates:sync`
- Buat 2 template (romantic, elegant) dengan minimal 4 section
- Halaman pilih template + preview dummy

**Week 2**
- Preview interaktif (isi nama, tanggal, realtime update via Mustache.js)
- sessionStorage flow → checkout → restore data
- Order + payment via Midtrans
- Aktivasi fitur otomatis setelah bayar

**Week 3**
- Dashboard: editor konten (nama, tanggal, venue, love story)
- Upload foto (cover + galeri), upload musik
- Reorder section, toggle ornamen
- Publish undangan ke subdomain

**Week 4**
- Manajemen tamu (tambah manual + import CSV)
- RSVP system + personal link (?to=unique_code)
- Amplop digital (QRIS, nomor rekening, e-wallet)
- Admin panel basic (FilamentPHP): kelola template, produk, harga, user

### Phase 2 — Add-on & Polish (2 minggu)

- Custom domain add-on (setting dari dashboard, instruksi DNS)
- Managed setup add-on (flag di admin, admin kelola manual)
- QR code check-in
- Extra storage add-on
- Sentry + health check endpoint
- WhatsApp share link

### Phase 3 — Growth (ongoing)

- Template ke-3, ke-4, dst (tanpa sentuh kode)
- Analytics dashboard (view count, RSVP rate)
- Musik dari library bawaan platform (tidak perlu upload)
- Video background support
- AI generate love story (opsional)

---

## Checklist Sebelum Launch

- [ ] 2 template selesai dan tampil benar di mobile
- [ ] Preview interaktif berjalan tanpa login
- [ ] sessionStorage flow tidak kehilangan data saat checkout
- [ ] Payment Midtrans ditest dengan sandbox (semua metode)
- [ ] Webhook idempotent (test kirim 2x, fitur tidak dobel aktif)
- [ ] Upload file validasi MIME (bukan ekstensi)
- [ ] Subdomain routing berjalan (termasuk di local dev)
- [ ] Wildcard SSL aktif di staging
- [ ] Sentry tersambung
- [ ] Rate limiting aktif di endpoint publik
- [ ] Admin panel bisa ubah harga produk tanpa deploy
- [ ] `php artisan templates:sync` bisa tambah template baru tanpa restart server