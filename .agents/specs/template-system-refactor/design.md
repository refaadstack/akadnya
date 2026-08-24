# Design Document: Template System Refactor

## Overview

Refaktor ini mengganti tiga komponen inti dari Template_System MyAkad:

1. **Penyimpanan**: dari `storage/templates/{slug}/` ke `public/templates/{slug}/` — menghilangkan ketergantungan pada symlink Apache yang tidak kompatibel di Windows.
2. **Render Engine**: dari custom `MustacheNormalizer` + `\Mustache_Engine` ke `Blade::render()` — menggunakan engine bawaan Laravel yang sudah teruji dan dapat di-debug.
3. **Upload Workflow**: dari sync manual via Artisan ke upload ZIP dari Filament admin panel — memungkinkan administrator mengelola template tanpa akses server.

Tidak ada perubahan database schema. Semua tabel (`templates`, `template_sections`, `template_ornaments`) tetap digunakan seperti sekarang.

---

## Architecture

### Alur Saat Ini (Before)

```mermaid
flowchart TD
    A[Admin: Artisan templates:sync] --> B[TemplateService::syncTemplates]
    B --> C[Scan storage/templates/]
    C --> D[Parse template.json]
    D --> E[Upsert DB: templates, sections, ornaments]

    F[User: GET /i/{subdomain}] --> G[PublicInvitationController::show]
    G --> H[TemplateService::loadSectionHtml]
    H --> I[storage/templates/{slug}/sections/]
    I --> J[MustacheNormalizer::normalize]
    J --> K[Mustache_Engine::render]
    K --> L[HTML Response]

    M[Admin: GET /templates/{slug}/preview] --> N[TemplateController::preview]
    N --> O[storage/templates/{slug}/assets/]
    O --> P[Inline CSS/JS ke HTML]
    P --> Q[HTML Response]
```

### Alur Setelah Refaktor (After)

```mermaid
flowchart TD
    A[Admin: Upload ZIP via Filament] --> B[TemplateUploadAction]
    B --> C[TemplateService::processUpload]
    C --> D[Validasi ZIP: template.json, sections, ornaments, assets]
    D --> E[Ekstrak ke temp dir]
    E --> F[Pindah atomik ke public/templates/{slug}/]
    F --> G[Upsert DB: templates, sections, ornaments]

    H[User: GET /i/{subdomain}] --> I[PublicInvitationController::show]
    I --> J[BladeRenderService::renderInvitation]
    J --> K[DataContractBuilder::build dari InvitationContent]
    K --> L[Blade::render dari public/templates/{slug}/sections/]
    L --> M[Wrap dalam div.template-{slug}]
    M --> N[HTML Response tanpa CSS global]

    O[Admin: GET /templates/{slug}/preview] --> P[TemplateController::preview]
    P --> Q[BladeRenderService::renderPreview]
    Q --> R[DataContractBuilder::buildDummy]
    R --> S[Blade::render dari public/templates/{slug}/sections/]
    S --> T[Render dalam iframe di admin panel]
```

### Komponen Baru

| Komponen | Lokasi | Tanggung Jawab |
|---|---|---|
| `BladeRenderService` | `app/Services/BladeRenderService.php` | Render sections/ornaments via `Blade::render()`, wrap CSS isolation |
| `DataContractBuilder` | `app/Services/DataContractBuilder.php` | Membangun array variabel Data_Contract dari `InvitationContent` |
| `TemplateUploadAction` | `app/Filament/Resources/Templates/Actions/TemplateUploadAction.php` | Filament action untuk upload ZIP |
| `TemplateZipValidator` | `app/Services/TemplateZipValidator.php` | Validasi struktur dan keamanan file ZIP |

### Komponen yang Dimodifikasi

| Komponen | Perubahan |
|---|---|
| `TemplateService` | Ganti path `storage_path()` ke `public_path()`, tambah `processUpload()`, hapus `loadSectionHtml()`/`loadOrnamentHtml()` |
| `Template` model | Ganti `getFolderPath()` ke `public_path()` |
| `PublicInvitationController` | Ganti `buildHtmlStructure()` + Mustache ke `BladeRenderService` |
| `TemplateController` | Ganti `renderTemplatePreview()` + Mustache ke `BladeRenderService` |
| `TemplateForm` | Tambah `FileUpload` component untuk ZIP |
| `SyncTemplates` command | Update path scan ke `public/templates/` |

### Komponen yang Dihapus

- `app/Services/MustacheNormalizer.php` — tidak diperlukan setelah migrasi ke Blade
- Semua `use \Mustache_Engine` di `PublicInvitationController` dan `TemplateController`
- `import Mustache from 'mustache'` di `resources/js/pages/Templates/Preview.vue`

---

## Vue Frontend Changes

### Preview.vue — Sebelum vs Sesudah

**Sebelum**: `Preview.vue` menerima `sections` dan `ornaments` sebagai props dari server (berisi HTML mentah), lalu merender langsung di browser menggunakan `Mustache.js`. Ini menyebabkan template harus support dua syntax sekaligus (Blade untuk server, Mustache untuk client).

**Sesudah**: `Preview.vue` hanya menampilkan form input data. Setiap kali data berubah, Vue mengirim POST ke `Preview_API` dan menampilkan HTML hasil render di `<iframe srcdoc>`.

```
Preview.vue (baru)
├── Form sidebar (data input: nama, tanggal, venue, foto)
├── Debounced watcher → POST /api/templates/{slug}/preview
├── Loading state saat request berlangsung
└── <iframe srcdoc="..."> untuk menampilkan HTML hasil render
```

### Preview API Endpoint

```
POST /api/templates/{slug}/preview
Content-Type: application/json
Authorization: tidak diperlukan (public endpoint)

Request body:
{
  "bride_name": "Siti Nurhaliza",
  "groom_name": "Budi Santoso",
  "akad_datetime": "2025-06-14T09:00:00",
  "akad_venue": "Masjid Al-Ikhlas",
  ...semua field Data_Contract yang relevan...
}

Response:
{
  "html": "<html>...</html>"
}
```

### Komponen Vue yang Dimodifikasi

| Komponen | Perubahan |
|---|---|
| `resources/js/pages/Templates/Preview.vue` | Hapus Mustache.js, ganti rendering ke iframe + API call |
| `resources/js/pages/Templates/Index.vue` | Tidak berubah (hanya tampilkan card template) |

### Props Preview.vue (baru)

```typescript
// Sebelum: menerima sections[], ornaments[], dummyData
// Sesudah: hanya menerima metadata template
interface Props {
  template: {
    id: number
    slug: string
    name: string
    price: number
    is_free: boolean
  }
}
```

Server tidak perlu lagi mengirim HTML sections ke Vue — semua rendering dilakukan server-side via API.

---

## Components and Interfaces

### BladeRenderService

Service utama yang menggantikan logika Mustache rendering.

```php
class BladeRenderService
{
    /**
     * Render halaman undangan publik lengkap.
     *
     * @param  array<string, mixed>  $data  Data_Contract dari DataContractBuilder
     */
    public function renderInvitation(Invitation $invitation, array $data): string;

    /**
     * Render preview template untuk admin panel.
     *
     * @param  array<string, mixed>  $dummyData  Dummy data dari DataContractBuilder::buildDummy()
     */
    public function renderPreview(Template $template, array $dummyData): string;

    /**
     * Render satu section menggunakan Blade::render().
     * Mengembalikan string kosong jika file tidak ditemukan.
     */
    public function renderSection(Template $template, string $sectionFile, array $data): string;

    /**
     * Render satu ornament menggunakan Blade::render().
     */
    public function renderOrnament(Template $template, string $ornamentFile, array $data): string;

    /**
     * Wrap konten HTML dalam div isolasi CSS.
     * Output: <div class="template-{slug}">...</div>
     */
    public function wrapWithCssIsolation(string $html, string $slug): string;

    /**
     * Bangun HTML head dengan link ke asset template (bukan inline).
     */
    public function buildAssetTags(Template $template): string;
}
```

### DataContractBuilder

Membangun array variabel yang dijamin selalu lengkap (tidak ada key yang missing).

```php
class DataContractBuilder
{
    /**
     * Bangun Data_Contract dari InvitationContent nyata.
     *
     * @return array<string, mixed>  Semua variabel Data_Contract, null jika field kosong
     */
    public function build(Invitation $invitation, ?string $guestName = null): array;

    /**
     * Bangun Data_Contract dengan dummy data untuk preview.
     *
     * @return array<string, mixed>
     */
    public function buildDummy(): array;

    /**
     * Bangun variabel turunan dari datetime (akad atau reception).
     *
     * @return array<string, string>  date, month, year, day, datetime_formatted, time
     */
    public function buildDatetimeVariables(string $prefix, ?\Carbon\Carbon $datetime): array;
}
```

### TemplateZipValidator

Memvalidasi struktur dan keamanan file ZIP sebelum ekstraksi.

```php
class TemplateZipValidator
{
    /**
     * Validasi ZIP secara lengkap.
     *
     * @return array{valid: bool, errors: string[], manifest: array<string, mixed>|null}
     */
    public function validate(string $zipPath): array;

    /**
     * Periksa apakah ada path traversal dalam ZIP.
     * Menolak path yang mengandung '..' atau dimulai dengan '/'.
     */
    public function containsPathTraversal(\ZipArchive $zip): bool;

    /**
     * Validasi bahwa file HTML adalah teks valid (bukan binary).
     */
    public function isValidTextFile(\ZipArchive $zip, string $entryName): bool;
}
```

### TemplateService (dimodifikasi)

```php
class TemplateService
{
    /**
     * Proses upload ZIP: validasi, ekstrak atomik, upsert database.
     *
     * @return array{success: bool, message: string, template?: Template}
     */
    public function processUpload(string $zipPath): array;

    /**
     * Scan public/templates/ dan sync ke database.
     *
     * @return array{synced: int, errors: string[]}
     */
    public function syncTemplates(): array;

    /**
     * Parse dan validasi template.json.
     *
     * @return array<string, mixed>|null  null jika tidak valid
     */
    public function parseTemplateJson(string $folderPath): ?array;
}
```

### TemplateUploadAction (Filament)

Action Filament yang dipasang di `TemplateForm` untuk upload ZIP.

```php
// Dipasang sebagai action di halaman Create/Edit Template
// Menggunakan Filament FileUpload component dengan:
// - acceptedFileTypes: ['application/zip', 'application/x-zip-compressed']
// - maxSize: 51200 (50MB dalam KB)
// - Memanggil TemplateService::processUpload() setelah upload
```

### TemplatePreviewController (baru)

Controller untuk Preview API yang dipanggil oleh `Preview.vue`.

```php
class TemplatePreviewController extends Controller
{
    /**
     * Render template preview dengan data dari form Vue.
     * Public endpoint — tidak memerlukan autentikasi.
     *
     * POST /api/templates/{slug}/preview
     *
     * @return \Illuminate\Http\JsonResponse  { html: string }
     */
    public function render(Request $request, string $slug): JsonResponse;
}
```

Route yang ditambahkan:
```php
// routes/api.php atau routes/web.php
Route::post('/api/templates/{slug}/preview', [TemplatePreviewController::class, 'render'])
    ->name('api.templates.preview');
```

---

## Data Models

### Template (tidak berubah)

```
templates
├── id
├── slug (unique, 100)
├── name
├── thumbnail_url (nullable)
├── version (default: '1.0.0')
├── is_free (boolean)
├── price (decimal 12,2)
├── is_active (boolean)
├── synced_at (nullable timestamp)
└── timestamps
```

### TemplateSection (tidak berubah)

```
template_sections
├── id
├── template_id (FK → templates)
├── file (100) — nama file HTML, contoh: 'cover.html'
├── label (100) — label display, contoh: 'Cover'
├── sort_order (tinyint unsigned)
├── is_required (boolean)
└── created_at
```

### TemplateOrnament (tidak berubah)

```
template_ornaments
├── id
├── template_id (FK → templates)
├── file (100) — nama file HTML
├── label (100)
├── position (enum: top, bottom, between, overlay)
├── default_active (boolean)
└── created_at
```

### Struktur Direktori Template (baru)

```
public/
└── templates/
    ├── DATA_CONTRACT.md          ← dokumentasi variabel untuk template designer
    └── {slug}/
        ├── template.json         ← manifest template
        ├── sections/
        │   ├── cover.html        ← file Blade HTML (bukan .blade.php)
        │   ├── gallery.html
        │   └── rsvp.html
        ├── ornaments/
        │   ├── flower-top.html
        │   └── border.html
        └── assets/
            ├── style.css         ← wajib ada, semua selector harus prefix .template-{slug}
            ├── script.js         ← opsional
            └── images/           ← opsional
```

### Format template.json

```json
{
    "slug": "elegant-rose",
    "name": "Elegant Rose",
    "version": "1.0.0",
    "is_free": false,
    "price": 75000,
    "thumbnail": "assets/images/thumbnail.jpg",
    "sections": [
        { "file": "cover.html", "label": "Cover", "sort_order": 1, "is_required": true },
        { "file": "story.html", "label": "Love Story", "sort_order": 2, "is_required": false },
        { "file": "gallery.html", "label": "Gallery", "sort_order": 3, "is_required": false },
        { "file": "rsvp.html", "label": "RSVP", "sort_order": 4, "is_required": true }
    ],
    "ornaments": [
        { "file": "flower-top.html", "label": "Bunga Atas", "position": "top", "default_active": true },
        { "file": "border.html", "label": "Border", "position": "overlay", "default_active": false }
    ]
}
```

### Data Contract

Variabel yang **selalu tersedia** saat rendering (nilai bisa `null` atau string kosong, tapi key selalu ada):

```php
// Mempelai
$bride_name, $bride_father, $bride_mother, $bride_photo_url
$groom_name, $groom_father, $groom_mother, $groom_photo_url

// Akad — variabel turunan dari akad_datetime
$akad_datetime_formatted  // "Sabtu, 14 Juni 2025"
$akad_time                // "09:00 WIB"
$akad_date                // "14"
$akad_month               // "Juni"
$akad_year                // "2025"
$akad_day                 // "Sabtu"
$akad_venue, $akad_maps_url

// Resepsi — variabel turunan dari reception_datetime
$reception_datetime_formatted, $reception_time
$reception_date, $reception_month, $reception_year, $reception_day
$reception_venue, $reception_maps_url

// Media
$cover_photo_url, $music_url

// Konten
$love_story, $special_message

// Galeri
$gallery  // array of ['url' => string, 'caption' => string]

// RSVP
$rsvp_action  // URL route RSVP
$csrf_token

// Tamu
$guest_name  // dari query param ?name=
```

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

Sebelum menulis properties final, berikut analisis redundansi:

- **2.6 dan 6.6** (ordering sections) dapat digabung: keduanya menguji bahwa sections dirender dalam urutan `sort_order`. Satu property yang mencakup keduanya lebih baik.
- **3.3 dan 3.4** (datetime variables untuk akad dan reception) dapat digabung: keduanya menguji transformasi datetime ke variabel turunan. Satu property "untuk sembarang datetime" mencakup keduanya.
- **5.1 dan 5.2** (validasi sections dan ornaments) dapat digabung: keduanya menguji bahwa file yang terdaftar di manifest harus ada. Satu property "untuk sembarang daftar file" mencakup keduanya.
- **4.8** (cleanup on error) dan **4.2** (atomik extraction) dapat digabung: keduanya berkaitan dengan atomicity — tidak ada state parsial.
- **3.6** (semua variabel selalu ada) dan **2.3** (semua variabel Data_Contract tersedia) adalah property yang sama — digabung menjadi satu.

---

### Property 1: Data Contract Completeness

*For any* `InvitationContent` (termasuk yang memiliki semua field `null`), `DataContractBuilder::build()` harus mengembalikan array yang mengandung **semua** key yang didefinisikan dalam Data_Contract, tanpa exception.

**Validates: Requirements 2.3, 3.1, 3.6**

---

### Property 2: Datetime Variable Derivation

*For any* nilai datetime yang valid (akad atau reception), `DataContractBuilder::buildDatetimeVariables()` harus menghasilkan variabel turunan (`date`, `month`, `year`, `day`, `datetime_formatted`, `time`) yang konsisten satu sama lain — yaitu, nilai-nilai tersebut harus merepresentasikan tanggal yang sama.

**Validates: Requirements 3.3, 3.4**

---

### Property 3: Gallery Array Structure

*For any* koleksi `InvitationGallery` dengan N item, `DataContractBuilder::build()` harus menghasilkan `$gallery` sebagai array dengan tepat N elemen, di mana setiap elemen memiliki key `url` dan `caption`.

**Validates: Requirements 3.5**

---

### Property 4: Section Rendering Order

*For any* set `InvitationSection` dengan nilai `sort_order` yang bervariasi dan `is_visible` yang bervariasi, `BladeRenderService::renderInvitation()` harus merender sections dalam urutan `sort_order` ascending, dan hanya sections dengan `is_visible = true` yang muncul dalam output.

**Validates: Requirements 2.6, 6.6**

---

### Property 5: CSS Isolation Wrapper

*For any* template dengan slug apapun, output dari `BladeRenderService::wrapWithCssIsolation()` harus mengandung elemen `<div class="template-{slug}">` yang membungkus seluruh konten.

**Validates: Requirements 8.1**

---

### Property 6: Asset URL Generation

*For any* slug template dan nama file asset yang valid, URL yang dihasilkan oleh `asset('templates/{slug}/assets/{filename}')` harus mengandung path `templates/{slug}/assets/{filename}` dan dapat di-resolve di semua environment.

**Validates: Requirements 1.6**

---

### Property 7: ZIP Validation Reports All Missing Files

*For any* file ZIP yang memiliki N file yang terdaftar di `template.json` tetapi tidak ada di dalam ZIP, `TemplateZipValidator::validate()` harus mengembalikan array `errors` yang mengandung semua N file yang hilang dalam satu kali validasi (bukan berhenti di error pertama).

**Validates: Requirements 5.1, 5.2, 5.4**

---

### Property 8: Path Traversal Rejection

*For any* file ZIP yang mengandung entry dengan path yang mengandung `..` atau dimulai dengan `/`, `TemplateZipValidator::containsPathTraversal()` harus mengembalikan `true` dan proses upload harus ditolak.

**Validates: Requirements 5.5**

---

### Property 9: Upload Atomicity (No Partial State)

*For any* error yang terjadi selama proses `TemplateService::processUpload()` (baik saat validasi, ekstraksi, maupun sinkronisasi database), tidak boleh ada direktori sementara yang tersisa di filesystem setelah proses selesai.

**Validates: Requirements 4.2, 4.8**

---

### Property 10: Sync Idempotency

*For any* template yang valid di `public/templates/`, menjalankan `TemplateService::syncTemplates()` dua kali berturut-turut harus menghasilkan jumlah record `Template`, `TemplateSection`, dan `TemplateOrnament` yang sama di database (idempotent).

**Validates: Requirements 7.3**

---

### Property 11: Template Deletion Protection

*For any* template yang memiliki N undangan dengan status `published` (N > 0), operasi delete dari admin panel harus ditolak dan pesan error harus menyebutkan nilai N yang tepat.

**Validates: Requirements 9.4**

---

### Property 12: Deactivation Visibility

*For any* template yang `is_active = true`, setelah administrator menonaktifkannya, template tersebut tidak boleh muncul dalam query `Template::active()` yang digunakan untuk halaman pemilihan template pengguna.

**Validates: Requirements 9.2**

---

### Property 13: Preview API Consistency

*For any* data form yang dikirim ke `POST /api/templates/{slug}/preview`, HTML yang dikembalikan harus mengandung nilai dari data tersebut (misalnya `bride_name` harus muncul di HTML output), sama seperti jika undangan nyata dirender dengan data yang sama.

**Validates: Requirements 10.2, 10.3**

---

## Error Handling

### Hierarki Error

```
TemplateException (base)
├── TemplateNotFoundException       — template tidak ditemukan di filesystem
├── TemplateManifestException       — template.json tidak valid atau tidak ada
├── TemplateValidationException     — struktur ZIP tidak lengkap (berisi daftar file hilang)
├── TemplateSecurityException       — path traversal terdeteksi
└── TemplateRenderException         — error saat Blade::render()
```

### Strategi Error per Komponen

**TemplateZipValidator**
- Kumpulkan semua error validasi sebelum return (jangan fail-fast)
- Return `['valid' => false, 'errors' => [...semua error...]]`

**TemplateService::processUpload()**
- Selalu cleanup temp dir di `finally` block
- Return `['success' => false, 'message' => '...']` untuk error yang diharapkan
- Log exception untuk error yang tidak diharapkan

**BladeRenderService::renderSection()**
- Jika file section tidak ditemukan: return string kosong, log warning
- Jika `Blade::render()` melempar exception: catch, log error, return string kosong
- Variabel Blade yang tidak dikenal: Blade secara default tidak melempar exception untuk variabel undefined jika menggunakan `{{ $var ?? '' }}`

**PublicInvitationController**
- Jika direktori template tidak ditemukan: `abort(500, 'Template files not found')`
- Jika template tidak aktif: `abort(404)`

**SyncTemplates command**
- Lanjutkan ke direktori berikutnya jika satu direktori gagal
- Tampilkan ringkasan di akhir: berhasil N, gagal M dengan daftar error

### Blade Variable Safety

Untuk memastikan variabel yang tidak ada di Data_Contract tidak melempar exception, `BladeRenderService` akan menggunakan `Blade::render()` dengan semua variabel dari Data_Contract sebagai `$__data`. Template designer disarankan menggunakan `{{ $var ?? '' }}` untuk variabel opsional.

---

## Testing Strategy

### Pendekatan Dual Testing

Fitur ini menggunakan dua lapisan testing yang saling melengkapi:

1. **Unit/Feature tests** — untuk contoh spesifik, edge cases, dan error conditions
2. **Property-based tests** — untuk memverifikasi properties universal di atas berbagai input

Library PBT yang digunakan: **[eris/eris](https://github.com/giorgiosironi/eris)** (PHP property-based testing library).

### Unit & Feature Tests

**TemplateZipValidatorTest** (`tests/Unit/TemplateZipValidatorTest.php`)
- ZIP tanpa `template.json` → error yang tepat
- ZIP dengan `template.json` tanpa field `slug`/`name` → error yang tepat
- ZIP tanpa `assets/style.css` → error yang tepat
- ZIP dengan path traversal (`../../etc/passwd`) → ditolak
- ZIP valid lengkap → `valid = true`

**TemplateServiceTest** (`tests/Feature/TemplateServiceTest.php`)
- Upload ZIP valid → record database terbuat, file ada di `public/templates/{slug}/`
- Upload ZIP invalid → tidak ada file parsial di filesystem
- Sync dari direktori kosong → `synced = 0`
- Sync dengan direktori valid → record terbuat/diupdate

**BladeRenderServiceTest** (`tests/Unit/BladeRenderServiceTest.php`)
- Section file tidak ditemukan → return string kosong, tidak exception
- Wrapper CSS isolation → output mengandung `<div class="template-{slug}">`
- Asset tags → output mengandung `<link>` dan `<script>` dengan URL absolut

**PublicInvitationControllerTest** (`tests/Feature/PublicInvitationControllerTest.php`)
- Template files tidak ada → HTTP 500
- Undangan tidak published → HTTP 404
- Undangan published → HTTP 200 dengan HTML yang mengandung data mempelai

**TemplateAdminTest** (`tests/Feature/TemplateAdminTest.php`)
- Delete template dengan undangan aktif → ditolak dengan pesan error
- Delete template tanpa undangan aktif → berhasil, direktori terhapus
- Deactivate template → `is_active = false`, tidak muncul di `Template::active()`

### Property-Based Tests

Setiap property test dikonfigurasi minimum **100 iterasi**.

**DataContractBuilderPropertyTest** (`tests/Unit/DataContractBuilderPropertyTest.php`)

```php
// Feature: template-system-refactor, Property 1: Data Contract Completeness
// Generate InvitationContent dengan field null secara acak
// Verifikasi semua key Data_Contract selalu ada

// Feature: template-system-refactor, Property 2: Datetime Variable Derivation
// Generate datetime acak (Carbon instance)
// Verifikasi variabel turunan konsisten satu sama lain

// Feature: template-system-refactor, Property 3: Gallery Array Structure
// Generate koleksi InvitationGallery dengan N item acak
// Verifikasi $gallery memiliki tepat N elemen dengan key url dan caption
```

**BladeRenderServicePropertyTest** (`tests/Unit/BladeRenderServicePropertyTest.php`)

```php
// Feature: template-system-refactor, Property 4: Section Rendering Order
// Generate set sections dengan sort_order dan is_visible acak
// Verifikasi urutan dan filtering dalam output

// Feature: template-system-refactor, Property 5: CSS Isolation Wrapper
// Generate slug acak (alphanumeric + dash)
// Verifikasi output mengandung div.template-{slug}

// Feature: template-system-refactor, Property 6: Asset URL Generation
// Generate slug dan filename acak
// Verifikasi URL mengandung path yang benar
```

**TemplateZipValidatorPropertyTest** (`tests/Unit/TemplateZipValidatorPropertyTest.php`)

```php
// Feature: template-system-refactor, Property 7: ZIP Validation Reports All Missing Files
// Generate daftar file yang terdaftar di manifest tapi tidak ada di ZIP
// Verifikasi semua file dilaporkan dalam satu validasi

// Feature: template-system-refactor, Property 8: Path Traversal Rejection
// Generate berbagai pola path traversal (../x, ../../x, /etc/x, dll)
// Verifikasi semua ditolak
```

**TemplateServicePropertyTest** (`tests/Feature/TemplateServicePropertyTest.php`)

```php
// Feature: template-system-refactor, Property 9: Upload Atomicity
// Inject error di berbagai titik proses upload
// Verifikasi tidak ada temp dir yang tersisa

// Feature: template-system-refactor, Property 10: Sync Idempotency
// Generate template valid, jalankan sync dua kali
// Verifikasi jumlah record sama

// Feature: template-system-refactor, Property 11: Template Deletion Protection
// Generate template dengan N undangan published (N > 0)
// Verifikasi delete ditolak dengan pesan yang menyebutkan N

// Feature: template-system-refactor, Property 12: Deactivation Visibility
// Generate template aktif, deactivate
// Verifikasi tidak muncul di Template::active()
```

### Catatan Implementasi

- Gunakan `Blade::render()` dengan `['__env' => app('view')]` untuk menghindari error "Undefined variable" pada variabel yang tidak digunakan di template
- Untuk testing `Blade::render()` dengan file HTML dari filesystem, gunakan `Blade::renderComponent()` atau buat Blade string dari `File::get()`
- Property tests untuk filesystem (upload, sync) menggunakan `Storage::fake()` atau direktori temp yang dibersihkan di `tearDown()`
