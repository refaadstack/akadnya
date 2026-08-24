# Implementation Plan: Template System Refactor

## Overview

Refaktor ini mengganti tiga komponen inti: penyimpanan asset dari `storage/templates/` ke `public/templates/`, render engine dari Mustache ke `Blade::render()`, dan workflow upload dari Artisan sync ke upload ZIP via Filament admin panel. Selain itu, fitur Interactive Preview diimplementasikan ulang — `Preview.vue` tidak lagi merender template di browser menggunakan Mustache.js, melainkan mengirim data ke `POST /api/templates/{slug}/preview` dan menampilkan HTML hasil render server di dalam `<iframe srcdoc>`. Implementasi dilakukan secara incremental — layer bawah (service, validator) dibangun lebih dulu sebelum layer atas (controller, Filament action, Vue).

## Tasks

- [x] 1. Buat exception hierarchy dan DataContractBuilder
  - [x] 1.1 Buat exception classes di `app/Exceptions/Template/`
    - Buat `TemplateException` sebagai base class
    - Buat `TemplateNotFoundException`, `TemplateManifestException`, `TemplateValidationException`, `TemplateSecurityException`, `TemplateRenderException` yang extend `TemplateException`
    - _Requirements: 4.6, 4.7, 4.8, 5.4, 5.5_

  - [x] 1.2 Buat `DataContractBuilder` di `app/Services/DataContractBuilder.php`
    - Implementasi `build(Invitation $invitation, ?string $guestName = null): array` — petakan semua field `InvitationContent` ke Data_Contract, termasuk variabel turunan datetime
    - Implementasi `buildDummy(): array` — pindahkan dummy data dari `TemplateController::getDummyData()` ke sini
    - Implementasi `buildDatetimeVariables(string $prefix, ?\Carbon\Carbon $datetime): array` — hasilkan `date`, `month`, `year`, `day`, `datetime_formatted`, `time` dalam Bahasa Indonesia
    - Pastikan semua key Data_Contract selalu ada meskipun nilai `null` (gunakan `?? null` untuk field opsional)
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

  - [x] 1.3 Tulis feature test untuk DataContractBuilder
    - Buat `tests/Feature/DataContractBuilderTest.php` dengan 6 test cases
    - Test: Data Contract Completeness — semua key selalu ada meskipun content null
    - Test: Data population dari InvitationContent
    - Test: Datetime variable derivation — variabel turunan konsisten
    - Test: Datetime null handling
    - Test: Gallery array structure
    - Test: Dummy data completeness
    - _Requirements: 3.1, 3.3, 3.4, 3.5, 3.6_

- [x] 2. Buat TemplateZipValidator
  - [x] 2.1 Buat `TemplateZipValidator` di `app/Services/TemplateZipValidator.php`
    - Implementasi `validate(string $zipPath): array` — kumpulkan semua error validasi sebelum return (tidak fail-fast), return `['valid' => bool, 'errors' => string[], 'manifest' => array|null]`
    - Implementasi `containsPathTraversal(\ZipArchive $zip): bool` — tolak path yang mengandung `..` atau dimulai dengan `/`
    - Implementasi `isValidTextFile(\ZipArchive $zip, string $entryName): bool` — verifikasi file adalah teks valid bukan binary
    - Validasi keberadaan `template.json`, field `slug`/`name`, semua section files, semua ornament files, dan `assets/style.css`
    - _Requirements: 4.3, 4.4, 4.6, 4.7, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6_

  - [x] 2.2 Tulis unit test untuk TemplateZipValidator
    - Buat `tests/Unit/TemplateZipValidatorTest.php` dengan 12 test cases
    - Test: ZIP tanpa `template.json` → error yang tepat
    - Test: ZIP dengan `template.json` tanpa field `slug`/`name` → error yang tepat
    - Test: ZIP tanpa `assets/style.css` → error yang tepat
    - Test: ZIP dengan path traversal (`../../etc/passwd`) → ditolak
    - Test: ZIP valid lengkap → `valid = true`
    - Test: Binary file detection
    - Test: Multiple missing files reported together
    - _Requirements: 4.6, 4.7, 5.1, 5.2, 5.3, 5.4, 5.5_

  - [ ]* 2.3 Tulis property test untuk TemplateZipValidator (Property 7, 8)
    - Buat `tests/Unit/TemplateZipValidatorPropertyTest.php` menggunakan eris/eris
    - **Property 7: ZIP Validation Reports All Missing Files** — generate daftar file yang terdaftar di manifest tapi tidak ada di ZIP, verifikasi semua dilaporkan dalam satu validasi
    - **Property 8: Path Traversal Rejection** — generate berbagai pola path traversal (`../x`, `../../x`, `/etc/x`), verifikasi semua ditolak
    - Minimum 100 iterasi per property
    - _Requirements: 5.1, 5.2, 5.4, 5.5_

- [x] 3. Refaktor TemplateService
  - [x] 3.1 Update `TemplateService` untuk mendukung `public/templates/`
    - Ganti semua `storage_path('templates')` ke `public_path('templates')` di `syncTemplates()` dan `parseTemplateJson()`
    - Hapus method `loadSectionHtml()` dan `loadOrnamentHtml()` (akan digantikan oleh `BladeRenderService`)
    - Tambah method `processUpload(string $zipPath): array` — validasi via `TemplateZipValidator`, ekstrak ke temp dir, pindah atomik ke `public/templates/{slug}/`, upsert database, cleanup di `finally` block
    - Gunakan `TemplateValidationException` dan `TemplateSecurityException` dari hierarchy yang sudah dibuat
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 7.1, 7.2, 7.3, 7.5, 7.6_

  - [x] 3.2 Update `Template` model
    - Ganti `getFolderPath()` dari `storage_path('templates/'.$this->slug)` ke `public_path('templates/'.$this->slug)`
    - _Requirements: 1.1_

  - [x] 3.3 Update `SyncTemplates` Artisan command
    - Update path scan dari `storage/templates/` ke `public/templates/` (sudah ditangani oleh perubahan `TemplateService`)
    - Update modal description di `TemplatesTable` dari `storage/templates` ke `public/templates`
    - _Requirements: 7.1, 7.4_

  - [x] 3.4 Tulis feature test untuk TemplateService
    - Buat `tests/Feature/TemplateServiceTest.php` dengan 5 test cases
    - Test: upload ZIP valid → record database terbuat, file ada di `public/templates/{slug}/`
    - Test: upload ZIP invalid → tidak ada file parsial di filesystem
    - Test: sync dari direktori kosong → `synced = 0`
    - Test: sync dengan direktori valid → record terbuat/diupdate
    - Test: upload replaces existing template directory
    - _Requirements: 4.2, 4.5, 4.8, 7.3_

  - [ ]* 3.5 Tulis property test untuk TemplateService (Property 9, 10)
    - Buat `tests/Feature/TemplateServicePropertyTest.php` menggunakan eris/eris
    - **Property 9: Upload Atomicity** — inject error di berbagai titik proses upload, verifikasi tidak ada temp dir yang tersisa
    - **Property 10: Sync Idempotency** — generate template valid, jalankan sync dua kali, verifikasi jumlah record sama
    - Minimum 100 iterasi per property
    - _Requirements: 4.2, 4.8, 7.3_

- [ ] 4. Checkpoint — Pastikan semua tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Buat BladeRenderService
  - [x] 5.1 Buat `BladeRenderService` di `app/Services/BladeRenderService.php`
    - Implementasi `renderSection(Template $template, string $sectionFile, array $data): string` — baca file dari `public/templates/{slug}/sections/{file}`, render via `Blade::render()`, return string kosong jika file tidak ditemukan (log warning)
    - Implementasi `renderOrnament(Template $template, string $ornamentFile, array $data): string` — baca file dari `public/templates/{slug}/ornaments/{file}`, render via `Blade::render()`
    - Implementasi `wrapWithCssIsolation(string $html, string $slug): string` — wrap dalam `<div class="template-{slug}">...</div>`
    - Implementasi `buildAssetTags(Template $template): string` — hasilkan `<link rel="stylesheet">` dan `<script src="...">` dengan URL dari `asset('templates/{slug}/assets/...')`
    - Implementasi `renderInvitation(Invitation $invitation, array $data): string` — render semua sections `is_visible = true` berurutan `sort_order`, render ornaments aktif, wrap dengan CSS isolation
    - Implementasi `renderPreview(Template $template, array $dummyData): string` — render semua sections berurutan `sort_order`, tambah preview banner fixed di atas
    - Catch `\Exception` di `renderSection()`/`renderOrnament()`, log error, return string kosong
    - _Requirements: 1.6, 1.7, 2.1, 2.2, 2.3, 2.4, 2.6, 2.7, 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 8.1, 8.3_

  - [x] 5.2 Tulis feature test untuk BladeRenderService
    - Buat `tests/Feature/BladeRenderServiceTest.php` dengan 10 test cases
    - Test: section file tidak ditemukan → return string kosong, tidak exception
    - Test: `wrapWithCssIsolation()` → output mengandung `<div class="template-{slug}">`
    - Test: `buildAssetTags()` → output mengandung `<link>` dan `<script>` dengan URL absolut
    - Test: Blade directives rendering (@if, @foreach)
    - Test: renderSection with data
    - Test: renderOrnament with data
    - _Requirements: 1.6, 1.7, 2.4, 8.1_

  - [ ]* 5.3 Tulis property test untuk BladeRenderService (Property 4, 5, 6)
    - Buat `tests/Unit/BladeRenderServicePropertyTest.php` menggunakan eris/eris
    - **Property 4: Section Rendering Order** — generate set sections dengan `sort_order` dan `is_visible` acak, verifikasi urutan dan filtering dalam output
    - **Property 5: CSS Isolation Wrapper** — generate slug acak (alphanumeric + dash), verifikasi output mengandung `div.template-{slug}`
    - **Property 6: Asset URL Generation** — generate slug dan filename acak, verifikasi URL mengandung path yang benar
    - Minimum 100 iterasi per property
    - _Requirements: 1.6, 2.6, 6.6, 8.1_

- [x] 6. Refaktor PublicInvitationController dan TemplateController
  - [x] 6.1 Refaktor `PublicInvitationController`
    - Inject `BladeRenderService` dan `DataContractBuilder` via constructor (hapus `TemplateService` jika tidak lagi digunakan)
    - Ganti method `buildHtmlStructure()` — gunakan `DataContractBuilder::build()` lalu `BladeRenderService::renderInvitation()`
    - Hapus method `normalizeToMustache()` dan semua `use \Mustache_Engine`
    - Jika direktori template tidak ditemukan: `abort(500, 'Template files not found')`
    - _Requirements: 1.7, 2.1, 2.5, 8.3_

  - [x] 6.2 Refaktor `TemplateController`
    - Inject `BladeRenderService` dan `DataContractBuilder` via constructor
    - Ganti method `renderTemplatePreview()` — gunakan `DataContractBuilder::buildDummy()` lalu `BladeRenderService::renderPreview()`
    - Hapus method `normalizeToMustache()`, `getDummyData()`, dan semua `use \Mustache_Engine`
    - Update method `preview()` agar tidak lagi mengirim `sections`, `ornaments`, dan `dummyData` sebagai props Inertia — cukup kirim metadata template (`id`, `slug`, `name`, `price`, `is_free`)
    - _Requirements: 2.7, 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 10.1_

  - [ ]* 6.3 Tulis feature test untuk PublicInvitationController
    - Buat `tests/Feature/PublicInvitationControllerTest.php`
    - Test: template files tidak ada → HTTP 500
    - Test: undangan tidak published → HTTP 404
    - Test: undangan published → HTTP 200 dengan HTML yang mengandung data mempelai
    - _Requirements: 1.7, 2.1, 2.6_

- [x] 7. Buat TemplatePreviewController (Preview API)
  - [x] 7.1 Buat `TemplatePreviewController` di `app/Http/Controllers/TemplatePreviewController.php`
    - Implementasi `render(Request $request, string $slug): JsonResponse`
    - Ambil template dari database berdasarkan `slug` (hanya yang `is_active = true`), return 404 jika tidak ditemukan
    - Bangun data dari request body menggunakan `DataContractBuilder` — merge data yang dikirim Vue dengan fallback dummy data untuk field yang tidak dikirim
    - Panggil `BladeRenderService::renderPreview()` dengan data tersebut
    - Return `response()->json(['html' => $html])`
    - Endpoint ini **tidak memerlukan autentikasi** (public endpoint sebelum pembelian)
    - _Requirements: 10.1, 10.2, 10.3, 10.9_

  - [x] 7.2 Daftarkan route Preview API
    - Tambah route `Route::post('/api/templates/{slug}/preview', [TemplatePreviewController::class, 'render'])->name('api.templates.preview')` di `routes/web.php` (tanpa middleware auth)
    - Pastikan route dikecualikan dari CSRF jika diperlukan (atau gunakan `withoutMiddleware`)
    - _Requirements: 10.1, 10.9_

  - [x] 7.3 Tulis feature test untuk TemplatePreviewController
    - Buat `tests/Feature/TemplatePreviewControllerTest.php` dengan 6 test cases
    - Test: POST dengan data valid → HTTP 200, response JSON mengandung key `html`
    - Test: POST ke slug yang tidak ada → HTTP 404
    - Test: HTML yang dikembalikan mengandung nilai `bride_name` yang dikirim
    - Test: endpoint dapat diakses tanpa autentikasi
    - Test: Data merging (user data + dummy data)
    - Test: Preview banner included
    - _Requirements: 10.1, 10.2, 10.9_

  - [ ]* 7.4 Tulis property test untuk TemplatePreviewController (Property 13)
    - Tambahkan ke `tests/Feature/TemplatePreviewControllerTest.php` atau buat `tests/Feature/TemplatePreviewControllerPropertyTest.php`
    - **Property 13: Preview API Consistency** — generate data form acak (bride_name, groom_name, akad_venue, dll), POST ke endpoint, verifikasi nilai-nilai tersebut muncul di HTML output
    - Minimum 100 iterasi
    - _Requirements: 10.2, 10.3_

- [x] 8. Refaktor Preview.vue — hapus Mustache.js, ganti ke iframe + API call
  - [x] 8.1 Refaktor `resources/js/pages/Templates/Preview.vue`
    - Hapus `import Mustache from 'mustache'` dan semua penggunaan `Mustache.render()`
    - Hapus props `sections`, `ornaments`, dan `dummyData` — komponen sekarang hanya menerima prop `template` (metadata: `id`, `slug`, `name`, `price`, `is_free`)
    - Tambah state: `iframeHtml` (string HTML dari API), `isLoading` (boolean), `previewError` (string|null)
    - Tambah fungsi `fetchPreview()` yang melakukan `POST /api/templates/{slug}/preview` dengan data dari form, set `iframeHtml` dari response `html`
    - Tambah debounced watcher pada `previewData` dengan delay 500ms yang memanggil `fetchPreview()`
    - Panggil `fetchPreview()` sekali di `onMounted()` untuk render awal
    - Ganti area template preview dengan `<iframe :srcdoc="iframeHtml" ...>` — tampilkan loading indicator saat `isLoading = true`
    - Tampilkan pesan error informatif di area iframe jika `previewError` tidak null
    - Pertahankan semua logika form sidebar (input data mempelai, tanggal, venue, foto), sessionStorage, dan tombol "Beli Sekarang" (simpan data ke sessionStorage lalu redirect ke `/checkout?template={slug}`)
    - Hapus computed `renderedSections` dan `ornamentsByPosition` yang tidak lagi diperlukan
    - _Requirements: 10.4, 10.5, 10.6, 10.7, 10.8, 10.10_

  - [ ]* 8.2 Tulis unit test untuk Preview.vue (opsional, jika ada test setup Vue)
    - Test: watcher debounce 500ms memanggil API setelah data berubah
    - Test: loading indicator muncul saat request berlangsung
    - Test: error state ditampilkan jika API gagal
    - _Requirements: 10.6, 10.7, 10.8_

- [x] 9. Hapus MustacheNormalizer dan dependensi Mustache
  - [x] 9.1 Hapus `app/Services/MustacheNormalizer.php`
    - Verifikasi tidak ada referensi lain ke `MustacheNormalizer` di codebase setelah langkah 6
    - Hapus file `app/Services/MustacheNormalizer.php`
    - _Requirements: 2.5_

  - [x] 9.2 Hapus dependensi `mustache/mustache` dari `composer.json`
    - Jalankan `composer remove mustache/mustache`
    - Verifikasi tidak ada `use \Mustache_Engine` yang tersisa di codebase
    - _Requirements: 2.5_

- [x] 10. Buat TemplateUploadAction dan update TemplateForm
  - [x] 10.1 Buat `TemplateUploadAction` di `app/Filament/Resources/Templates/Actions/TemplateUploadAction.php`
    - Buat sebagai Filament `Action` yang dapat dipasang di halaman Create/Edit Template
    - Gunakan `FileUpload` component dengan `acceptedFileTypes: ['application/zip', 'application/x-zip-compressed']` dan `maxSize: 51200` (50MB)
    - Panggil `TemplateService::processUpload()` setelah upload
    - Tampilkan notifikasi sukses dengan nama template jika berhasil
    - Tampilkan notifikasi error dengan pesan deskriptif jika gagal
    - _Requirements: 4.1, 4.9_

  - [x] 10.2 Update `TemplateForm` untuk menambah upload action
    - Tambah `TemplateUploadAction` ke form sebagai action yang dapat diakses dari halaman Create/Edit
    - _Requirements: 4.1_

  - [x] 10.3 Update `TemplatesTable` — ganti modal description dari `storage/templates` ke `public/templates`
    - Update teks di `toolbarActions` sync button
    - _Requirements: 7.1_

  - [x] 10.4 Tulis feature test untuk TemplateAdminTest
    - Buat `tests/Feature/TemplateAdminTest.php`
    - Test: delete template dengan undangan aktif → ditolak dengan pesan error yang menyebutkan jumlah N
    - Test: delete template tanpa undangan aktif → berhasil, direktori terhapus
    - Test: deactivate template → `is_active = false`, tidak muncul di `Template::active()`
    - _Requirements: 9.2, 9.3, 9.4, 9.5_

  - [ ]* 10.5 Tulis property test untuk admin operations (Property 11, 12)
    - Tambahkan ke `tests/Feature/TemplateServicePropertyTest.php` atau buat file baru
    - **Property 11: Template Deletion Protection** — generate template dengan N undangan published (N > 0), verifikasi delete ditolak dengan pesan yang menyebutkan N
    - **Property 12: Deactivation Visibility** — generate template aktif, deactivate, verifikasi tidak muncul di `Template::active()`
    - Minimum 100 iterasi per property
    - _Requirements: 9.2, 9.4_

- [x] 11. Buat DATA_CONTRACT.md dan update EditTemplate untuk delete protection
  - [x] 11.1 Buat `public/templates/DATA_CONTRACT.md`
    - Dokumentasikan semua variabel Data_Contract yang tersedia untuk template designer
    - Sertakan contoh penggunaan Blade syntax (`{{ $bride_name }}`, `@if($music_url)`, `@foreach($gallery as $photo)`)
    - _Requirements: 3.2_

  - [x] 11.2 Update `EditTemplate` page untuk delete protection
    - Override `DeleteAction` di `getHeaderActions()` untuk menolak delete jika template masih digunakan oleh undangan published
    - Tampilkan pesan error "Template tidak dapat dihapus karena masih digunakan oleh N undangan aktif"
    - Jika tidak ada undangan aktif: hapus record database dan direktori `public/templates/{slug}/`
    - _Requirements: 9.3, 9.4, 9.5_

- [x] 12. Checkpoint akhir — Pastikan semua tests pass
  - Semua tests terkait template refactor berhasil (50 tests, 193 assertions)
  - Template system refactor selesai dan siap digunakan

## Notes

- Tasks bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- eris/eris belum ada di `composer.json` — perlu ditambahkan ke `require-dev` sebelum menulis property tests: `composer require --dev eris/eris`
- `mustache/mustache` di `composer.json` dihapus di task 9.2 setelah semua penggunaan sudah diganti
- `Preview.vue` tidak lagi menerima props `sections`, `ornaments`, `dummyData` — `TemplateController::preview()` harus diupdate bersamaan (task 6.2 dan 8.1 harus dikerjakan bersama)
- Debounce 500ms di `Preview.vue` menggunakan `setTimeout`/`clearTimeout` atau composable `useDebounce` — tidak perlu library tambahan
- Setiap task mereferensikan requirements spesifik untuk traceability
- Checkpoints memastikan validasi incremental
- Property tests memvalidasi correctness properties universal
- Unit tests memvalidasi contoh spesifik dan edge cases

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2"] },
    { "id": 1, "tasks": ["1.3", "2.1", "3.2"] },
    { "id": 2, "tasks": ["2.2", "2.3", "3.1"] },
    { "id": 3, "tasks": ["3.3", "3.4", "3.5"] },
    { "id": 4, "tasks": ["5.1"] },
    { "id": 5, "tasks": ["5.2", "5.3", "6.1", "6.2"] },
    { "id": 6, "tasks": ["6.3", "7.1"] },
    { "id": 7, "tasks": ["7.2", "7.3", "7.4", "8.1"] },
    { "id": 8, "tasks": ["8.2", "9.1"] },
    { "id": 9, "tasks": ["9.2", "10.1"] },
    { "id": 10, "tasks": ["10.2", "10.3", "11.1", "11.2"] },
    { "id": 11, "tasks": ["10.4", "10.5"] }
  ]
}
```
