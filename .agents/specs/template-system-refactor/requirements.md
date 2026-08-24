# Requirements Document

## Introduction

Fitur ini merefaktor sistem template undangan digital pada aplikasi MyAkad. Sistem template saat ini memiliki beberapa masalah kritis: file template disimpan di `storage/templates/{slug}/` dan diakses via symlink Apache yang tidak bisa di-resolve sebagai junction di Windows, render engine menggunakan custom Mustache PHP parser (`MustacheNormalizer`) yang tidak konsisten, dan data mapping antara `InvitationContent` dengan variabel template tidak terdefinisi dengan jelas. Refaktor ini bertujuan mengganti mekanisme penyimpanan asset ke `public/templates/`, mengganti render engine ke Blade PHP, menstandarisasi kontrak data mapping, dan menambahkan fitur upload template via ZIP dari admin panel Filament.

## Glossary

- **Template_System**: Subsistem yang mengelola template undangan, termasuk penyimpanan file, sinkronisasi database, dan rendering HTML.
- **Template**: Paket desain undangan yang terdiri dari sections, ornaments, dan asset (CSS, JS, gambar).
- **Template_Section**: Bagian HTML dari sebuah template (contoh: cover, gallery, rsvp) yang dirender secara terpisah dan dapat diurutkan ulang.
- **Template_Ornament**: Elemen dekoratif HTML dari sebuah template (contoh: bunga, border) yang dapat diaktifkan/dinonaktifkan.
- **Template_Manifest**: File `template.json` di dalam paket template yang mendefinisikan metadata, daftar sections, dan daftar ornaments.
- **Render_Engine**: Komponen yang mengubah file HTML template beserta data undangan menjadi halaman HTML final yang ditampilkan ke tamu.
- **Data_Contract**: Daftar variabel Blade yang tersedia di dalam file HTML template, dipetakan dari `InvitationContent` dan relasi terkait.
- **Template_Package**: File ZIP yang berisi seluruh file template (template.json, sections/, ornaments/, assets/) yang diunggah via admin panel.
- **Admin_Panel**: Antarmuka Filament di `/admin` yang digunakan oleh administrator untuk mengelola template.
- **TemplateService**: Service PHP yang bertanggung jawab atas sinkronisasi, validasi, dan ekstraksi template.
- **PublicInvitationController**: Controller yang merender halaman undangan publik untuk tamu.
- **InvitationContent**: Model yang menyimpan data konten undangan (nama mempelai, tanggal, venue, dll).
- **Blade_Template**: File HTML template yang menggunakan sintaks Blade PHP (`@foreach`, `{{ $variable }}`) sebagai pengganti Mustache.
- **Interactive_Preview**: Halaman Vue (`/templates/{slug}/preview`) yang memungkinkan calon pengguna mengisi data sendiri dan melihat tampilan undangan sebelum membeli.
- **Preview_API**: Endpoint `POST /api/templates/{slug}/preview` yang menerima data form dari Vue, merender template via Blade di server, dan mengembalikan HTML string.
- **Preview_Iframe**: Elemen `<iframe>` di `Preview.vue` yang menampilkan HTML hasil render dari Preview_API secara terisolasi.

---

## Requirements

### Requirement 1: Penyimpanan Asset Template di Public Directory

**User Story:** Sebagai developer, saya ingin asset template (CSS, JS, gambar) disimpan di `public/templates/{slug}/assets/` agar dapat diakses langsung via URL tanpa memerlukan symlink atau konfigurasi server tambahan.

#### Acceptance Criteria

1. THE Template_System SHALL menyimpan semua file asset template (CSS, JS, gambar) di direktori `public/templates/{slug}/assets/`.
2. THE Template_System SHALL menyimpan file HTML sections di direktori `public/templates/{slug}/sections/`.
3. THE Template_System SHALL menyimpan file HTML ornaments di direktori `public/templates/{slug}/ornaments/`.
4. THE Template_System SHALL menyimpan file `template.json` di direktori `public/templates/{slug}/`.
5. WHEN sebuah asset template diminta via HTTP, THE Template_System SHALL mengembalikan file tersebut dengan HTTP status 200 tanpa memerlukan symlink atau konfigurasi Apache tambahan.
6. THE Template_System SHALL menghasilkan URL asset template menggunakan fungsi `asset('templates/{slug}/assets/{filename}')` sehingga URL dapat di-resolve di semua environment (Windows, Linux, production).
7. IF direktori `public/templates/{slug}/` tidak ditemukan saat rendering, THEN THE Render_Engine SHALL mengembalikan halaman error dengan pesan "Template files not found" dan HTTP status 500.

---

### Requirement 2: Blade sebagai Render Engine

**User Story:** Sebagai developer, saya ingin template dirender menggunakan Blade PHP agar sintaks template konsisten, dapat di-debug dengan mudah, dan tidak memerlukan custom parser yang rawan bug.

#### Acceptance Criteria

1. THE Render_Engine SHALL merender file HTML section template menggunakan `Blade::render()` dengan data yang dipetakan dari `InvitationContent`.
2. THE Render_Engine SHALL mendukung sintaks Blade standar di dalam file HTML template: `{{ $variable }}`, `@if`, `@foreach`, `@unless`, `@isset`.
3. WHEN sebuah section template dirender, THE Render_Engine SHALL menyediakan semua variabel dari Data_Contract sebagai variabel Blade yang tersedia.
4. IF sebuah variabel Blade di dalam template tidak terdapat dalam Data_Contract, THEN THE Render_Engine SHALL merender string kosong untuk variabel tersebut tanpa melempar exception.
5. THE Template_System SHALL menghapus kelas `MustacheNormalizer` dan semua penggunaan `\Mustache_Engine` dari codebase setelah migrasi selesai.
6. WHEN template dirender untuk halaman publik, THE Render_Engine SHALL merender semua sections yang `is_visible = true` secara berurutan berdasarkan `sort_order`.
7. WHEN template dirender untuk preview admin, THE Render_Engine SHALL merender semua sections berdasarkan `sort_order` menggunakan dummy data yang merepresentasikan data undangan nyata.

---

### Requirement 3: Data Contract yang Terdefinisi

**User Story:** Sebagai template designer, saya ingin mengetahui daftar variabel yang pasti tersedia saat membuat template agar tidak ada ketidakcocokan antara variabel di template dengan data yang dikirim oleh sistem.

#### Acceptance Criteria

1. THE Template_System SHALL mendefinisikan Data_Contract sebagai daftar variabel Blade yang selalu tersedia saat rendering, mencakup minimal: data mempelai (`$bride_name`, `$groom_name`, `$bride_father`, `$bride_mother`, `$groom_father`, `$groom_mother`, `$bride_photo_url`, `$groom_photo_url`), data acara (`$akad_datetime_formatted`, `$akad_time`, `$akad_venue`, `$akad_maps_url`, `$reception_datetime_formatted`, `$reception_time`, `$reception_venue`, `$reception_maps_url`), media (`$cover_photo_url`, `$music_url`), konten (`$love_story`, `$special_message`), galeri (`$gallery` sebagai array), dan RSVP (`$rsvp_action`, `$csrf_token`).
2. THE Template_System SHALL mendokumentasikan Data_Contract dalam file `public/templates/DATA_CONTRACT.md` yang dapat diakses oleh template designer.
3. WHEN `InvitationContent` memiliki field `akad_datetime`, THE Render_Engine SHALL menyediakan variabel turunan: `$akad_date` (tanggal), `$akad_month` (nama bulan dalam Bahasa Indonesia), `$akad_year` (tahun), `$akad_day` (nama hari dalam Bahasa Indonesia), `$akad_datetime_formatted` (format lengkap), dan `$akad_time` (format jam WIB).
4. WHEN `InvitationContent` memiliki field `reception_datetime`, THE Render_Engine SHALL menyediakan variabel turunan yang setara dengan variabel akad untuk reception.
5. THE Render_Engine SHALL menyediakan variabel `$gallery` sebagai array of objects dengan properti `url` dan `caption` yang dipetakan dari relasi `InvitationGallery`.
6. FOR ALL variabel dalam Data_Contract, THE Render_Engine SHALL memastikan variabel tersebut selalu ada (tidak `undefined`) meskipun nilainya `null` atau string kosong, sehingga template tidak perlu melakukan pengecekan keberadaan variabel.

---

### Requirement 4: Upload Template via ZIP dari Admin Panel

**User Story:** Sebagai administrator, saya ingin mengunggah template baru melalui admin panel dengan cara mengupload file ZIP agar proses penambahan template tidak memerlukan akses langsung ke server.

#### Acceptance Criteria

1. WHEN administrator mengakses halaman create/edit template di Admin_Panel, THE Admin_Panel SHALL menampilkan form upload file ZIP dengan validasi tipe file `application/zip` dan ukuran maksimal 50MB.
2. WHEN administrator mengunggah file ZIP yang valid, THE TemplateService SHALL mengekstrak isi ZIP ke direktori `public/templates/{slug}/` secara atomik (ekstrak ke direktori sementara terlebih dahulu, lalu pindahkan).
3. WHEN file ZIP diekstrak, THE TemplateService SHALL memvalidasi keberadaan file `template.json` di root ZIP sebelum melanjutkan proses ekstraksi ke direktori final.
4. WHEN `template.json` valid ditemukan, THE TemplateService SHALL membaca field `slug`, `name`, `version`, `is_free`, `price`, `sections`, dan `ornaments` dari file tersebut.
5. WHEN ekstraksi berhasil, THE TemplateService SHALL melakukan upsert record `Template` di database dan menyinkronkan records `TemplateSection` dan `TemplateOrnament` sesuai data dari `template.json`.
6. IF file ZIP tidak mengandung `template.json`, THEN THE TemplateService SHALL menghapus direktori sementara dan mengembalikan error "template.json tidak ditemukan di dalam ZIP".
7. IF `template.json` tidak memiliki field `slug` atau `name`, THEN THE TemplateService SHALL mengembalikan error "template.json tidak valid: field slug dan name wajib ada".
8. IF terjadi error saat ekstraksi atau sinkronisasi database, THEN THE TemplateService SHALL menghapus direktori sementara dan mengembalikan pesan error yang deskriptif tanpa meninggalkan file parsial.
9. WHEN upload berhasil, THE Admin_Panel SHALL menampilkan notifikasi sukses dengan nama template yang berhasil diupload.

---

### Requirement 5: Validasi Struktur Template Package

**User Story:** Sebagai administrator, saya ingin sistem memvalidasi struktur file ZIP template sebelum disimpan agar template yang tidak lengkap tidak masuk ke sistem dan menyebabkan error saat rendering.

#### Acceptance Criteria

1. WHEN sebuah Template_Package diproses, THE TemplateService SHALL memvalidasi bahwa setiap file yang terdaftar di `sections` dalam `template.json` memiliki file HTML yang sesuai di direktori `sections/` dalam ZIP.
2. WHEN sebuah Template_Package diproses, THE TemplateService SHALL memvalidasi bahwa setiap file yang terdaftar di `ornaments` dalam `template.json` memiliki file HTML yang sesuai di direktori `ornaments/` dalam ZIP.
3. WHEN sebuah Template_Package diproses, THE TemplateService SHALL memvalidasi keberadaan file `assets/style.css` di dalam ZIP.
4. IF validasi struktur gagal, THEN THE TemplateService SHALL mengembalikan daftar semua file yang hilang dalam satu pesan error, bukan berhenti pada error pertama.
5. THE TemplateService SHALL menolak file ZIP yang mengandung path traversal (contoh: `../../etc/passwd`) dengan mengembalikan error "ZIP mengandung path yang tidak aman".
6. FOR ALL file HTML section dan ornament dalam Template_Package, THE TemplateService SHALL memverifikasi bahwa file tersebut adalah file teks valid (bukan binary) sebelum menyimpannya.

---

### Requirement 6: Preview Template yang Akurat

**User Story:** Sebagai administrator, saya ingin preview template menampilkan tampilan yang identik dengan tampilan undangan nyata agar saya dapat memverifikasi desain template sebelum dipublikasikan.

#### Acceptance Criteria

1. WHEN administrator mengakses halaman preview template, THE Render_Engine SHALL merender template menggunakan dummy data yang mencakup semua variabel dalam Data_Contract.
2. THE Render_Engine SHALL merender preview template menggunakan mekanisme rendering yang identik dengan rendering undangan publik (menggunakan Blade, bukan mekanisme terpisah).
3. WHEN template dirender untuk preview, THE Render_Engine SHALL memuat CSS dari `public/templates/{slug}/assets/style.css` menggunakan tag `<link rel="stylesheet">` dengan URL absolut, bukan dengan meng-inline konten CSS.
4. WHEN template dirender untuk preview, THE Render_Engine SHALL memuat JS dari `public/templates/{slug}/assets/script.js` menggunakan tag `<script src="...">` dengan URL absolut, bukan dengan meng-inline konten JS.
5. THE Render_Engine SHALL menampilkan banner "Preview Mode" yang fixed di bagian atas halaman preview agar administrator dapat membedakan preview dari undangan nyata.
6. WHEN template dirender untuk preview, THE Render_Engine SHALL merender semua sections yang terdaftar di database untuk template tersebut, terurut berdasarkan `sort_order`.

---

### Requirement 7: Sinkronisasi Template dari Filesystem

**User Story:** Sebagai developer, saya ingin dapat menyinkronkan template yang sudah ada di filesystem ke database menggunakan Artisan command agar proses migrasi dari sistem lama ke sistem baru dapat dilakukan tanpa kehilangan data.

#### Acceptance Criteria

1. THE Template_System SHALL menyediakan Artisan command `templates:sync` yang memindai direktori `public/templates/` dan menyinkronkan setiap template yang ditemukan ke database.
2. WHEN `templates:sync` dijalankan, THE TemplateService SHALL memproses setiap subdirektori di `public/templates/` yang mengandung file `template.json` yang valid.
3. WHEN `templates:sync` dijalankan, THE TemplateService SHALL melakukan upsert (update jika sudah ada, insert jika belum) berdasarkan field `slug`.
4. WHEN `templates:sync` selesai, THE Template_System SHALL menampilkan ringkasan: jumlah template yang berhasil disinkronkan dan daftar error untuk template yang gagal.
5. IF sebuah direktori template tidak memiliki `template.json` yang valid, THEN THE TemplateService SHALL mencatat error untuk direktori tersebut dan melanjutkan ke direktori berikutnya tanpa menghentikan proses sinkronisasi.
6. THE Template_System SHALL memperbarui field `synced_at` pada record `Template` setiap kali sinkronisasi berhasil dilakukan.

---

### Requirement 8: Isolasi CSS Template

**User Story:** Sebagai template designer, saya ingin CSS dari satu template tidak mempengaruhi tampilan template lain atau elemen UI aplikasi agar tidak terjadi konflik style.

#### Acceptance Criteria

1. THE Render_Engine SHALL membungkus seluruh konten HTML template (sections dan ornaments) di dalam sebuah elemen `<div>` dengan class unik berbasis slug template (contoh: `template-{slug}`).
2. THE Template_System SHALL mewajibkan semua selector CSS di dalam `assets/style.css` template untuk menggunakan prefix class `.template-{slug}` agar tidak terjadi konflik dengan CSS global aplikasi.
3. WHEN template dirender untuk halaman publik, THE Render_Engine SHALL tidak memuat CSS global aplikasi (Tailwind build) agar tidak terjadi konflik dengan CSS template.
4. WHEN template dirender untuk preview di admin panel, THE Render_Engine SHALL merender template di dalam elemen `<iframe>` yang terisolasi agar CSS template tidak mempengaruhi tampilan admin panel.

---

### Requirement 9: Pengelolaan Template di Admin Panel

**User Story:** Sebagai administrator, saya ingin dapat melihat, mengaktifkan/menonaktifkan, dan menghapus template dari admin panel agar pengelolaan template dapat dilakukan tanpa akses langsung ke server.

#### Acceptance Criteria

1. THE Admin_Panel SHALL menampilkan daftar semua template dengan kolom: nama, slug, versi, harga, status aktif, jumlah penggunaan (count undangan), dan tanggal sinkronisasi terakhir.
2. WHEN administrator menonaktifkan sebuah template, THE Admin_Panel SHALL mengubah field `is_active` menjadi `false` dan template tersebut tidak akan muncul di halaman pemilihan template untuk pengguna baru.
3. WHEN administrator menghapus sebuah template, THE Admin_Panel SHALL menampilkan konfirmasi yang menyebutkan jumlah undangan aktif yang menggunakan template tersebut sebelum melanjutkan penghapusan.
4. IF template yang akan dihapus masih digunakan oleh satu atau lebih undangan aktif (status `published`), THEN THE Admin_Panel SHALL menolak penghapusan dan menampilkan pesan error "Template tidak dapat dihapus karena masih digunakan oleh N undangan aktif".
5. WHEN administrator menghapus sebuah template yang tidak memiliki undangan aktif, THE Admin_Panel SHALL menghapus record database dan direktori `public/templates/{slug}/` secara bersamaan dalam satu operasi.

---

### Requirement 10: Interactive Preview via Server-Side Rendering

**User Story:** Sebagai calon pengguna, saya ingin mengisi data saya sendiri di halaman preview template dan melihat hasilnya secara akurat agar saya dapat memutuskan apakah template ini sesuai sebelum membeli.

#### Acceptance Criteria

1. THE Platform SHALL menyediakan endpoint `POST /api/templates/{slug}/preview` yang menerima data form dari Vue dan mengembalikan JSON `{ html: string }`.
2. WHEN endpoint preview dipanggil, THE Render_Engine SHALL merender template menggunakan data yang dikirim dari form, bukan dummy data.
3. THE Preview_API SHALL menggunakan mekanisme rendering yang identik dengan `BladeRenderService::renderPreview()` — Blade, CSS isolation wrapper, asset tags via URL.
4. WHEN `Preview.vue` menerima respons dari Preview_API, THE Platform SHALL menampilkan HTML hasil render di dalam elemen `<iframe srcdoc="...">` agar CSS template terisolasi dari halaman Vue.
5. THE Platform SHALL menghapus penggunaan `import Mustache from 'mustache'` dari `Preview.vue` setelah migrasi selesai.
6. WHEN pengguna mengubah data di form preview, THE Platform SHALL mengirim request baru ke Preview_API dengan debounce 500ms agar tidak terlalu banyak request.
7. WHEN Preview_API sedang memproses request, THE Platform SHALL menampilkan loading indicator di dalam area iframe.
8. IF Preview_API mengembalikan error, THE Platform SHALL menampilkan pesan error yang informatif di area preview tanpa crash halaman.
9. THE Preview_API SHALL tidak memerlukan autentikasi karena preview adalah fitur publik (sebelum pembelian).
10. WHEN pengguna mengklik "Beli sekarang" dari halaman preview, THE Platform SHALL menyimpan data form ke `sessionStorage` dan redirect ke `/checkout?template={slug}`.
