# Plan: Background Upload (Admin) + Polish Tipografi & Layout 12 Template

Status: **SELESAI** (26-08-06) — semua fase dieksekusi; dua deviasi font disetujui user.

## Keputusan yang sudah dikonfirmasi user
- Template: 11 adat/themed + 01kz (red-cream TIDAK disentuh)
- Background: **1 field** `background_url` per undangan
- Sumber gambar: form upload di admin (bukan saya yang sediakan gambar)
- Ornamen gambar: **skip**
- Polish: ganti pairing font per tema + rapiin ukuran/letter-spacing/spacing
- Font deviasi (user: "biarkan custom"): klasik-elegan = Playfair Display + Lato (bukan Cinzel); chinese-imperial-luxe = Source Han Serif SC + Cormorant (bukan Noto Serif SC) — alasan legibility & rendering
- Spacing: sebagian template 80-100px (target 72-96) — diterima sebagai acceptable

## Bonus (26-08-06, setelah plan selesai)
- Fix coret harga: cast `(float)` di WelcomeController/TemplateController/ProductController/CartService + test numeric (`61b76ff`)
- Guard `?? null` di semua `@if($qris_image_url)` / `@if($gopay_number|dana_number|ovo_number|gift_address|bank_name|account_number)` di template (red-cream, 01kz, melayu-jambi, sunda-merbak, chinese-imperial-luxe, betawi-adat, manado-sehati) — hilangkan "Undefined variable" saat render data parsial; regression test `renderSection emits no undefined variable warnings when payment data is missing` (BladeRenderServiceTest)

---

## Fase 1 — Fitur Upload Background (Admin)

Backend:
1. `database/migrations/2026_08_04_000001_add_background_url_to_invitation_contents_table.php` — kolom `background_url` string(500) nullable, after `cover_photo_url`
2. `app/Services/MediaService.php` — tambah arm `'background' => 'invitations/backgrounds'` di match upload() (baris 71-77)
3. `app/Http/Controllers/MediaController.php` — method `uploadBackground()` (5MB, image; pola sama dengan `uploadCover` baris 18-37)
4. `routes/web.php` — `POST media/upload/background` di blok media (baris 131-138)
5. `app/Http/Requests/InvitationContentRequest.php` — rule `background_url => nullable|url|max:500`
6. `app/Models/InvitationContent.php` — `background_url` ke `$fillable`
7. `app/Http/Controllers/EditorController.php` — tambah ke prop map index() (baris 41-75)

Frontend:
8. `resources/js/pages/Dashboard/Editor.vue` — kartu "Background Halaman" di card Media (baris 984-1331); pola sama dengan cover upload (991-1068): hidden input + preview + hapus + `uploadBackground()` via `uploadFile()` (115-133)

Contract & Template:
9. `app/Services/DataContractBuilder.php` — `'background_url'` di `build()` (67-148) DAN `buildEmptyPreviewContract()` (289-341)
10. 12 template (melayu-jambi, sunda-merbak, betawi-adat, minang-songket-gadang, balinese-saffron-pawiwahan, batak-heritage, bugis-royal-mappacci, chinese-imperial-luxe, klasik-elegan, manado-sehati, noir-luxe, 01kz1heyw9mwm46c7xgqvegrqh):
    - `@if($background_url)` → set CSS var `--bg-image: url("...")`
    - body / area luar wrapper desktop: `background-image: linear-gradient(overlay), var(--bg-image, none)`; `background-size:cover; background-position:center; background-attachment:fixed` (pola red-cream full.html:49,113)
    - opening screen: backdrop dengan dark overlay (fallback tetap aman saat kosong)

Test:
11. Feature test MediaController upload background (mirip test uploadCover yang ada)
12. Test Editor save dengan background_url
13. Test contract berisi background_url (build + empty)
14. Test render template berisi background URL saat diisi

## Fase 2 — Polish Tipografi & Layout (12 template)

Per template: ganti font pair + spacing rhythm + clamp heading + letter-spacing eyebrow + tombol; hapus "AI slop tell" kecil (tanpa redesign ornamen).

Pairing font (cek dulu cara load Google Fonts di tiap template):
- klasik-elegan → Cinzel + Lato
- betawi-adat → Cormorant Garamond + Poppins
- melayu-jambi → Marcellus + Source Sans 3 (+ Amiri utk arabic)
- sunda-merbak → Lora + Nunito Sans
- batak-heritage → Crimson Text + Source Sans 3
- minang-songket-gadang → Cormorant (pertahankan) + spacing dirapikan
- balinese-saffron-pawiwahan → Fraunces (pertahankan, refine)
- bugis-royal-mappacci → Marcellus + Plus Jakarta
- manado-sehati → DM Serif Display + DM Sans
- chinese-imperial-luxe → Noto Serif SC + Cormorant
- noir-luxe → refine monokrom
- 01kz1heyw9mwm46c7xgqvegrqh → refine

Spacing: padding section konsisten 72-96px; jarak header→konten; max-width kontainer.
Verifikasi render tiap template: kontrak kosong & terisi, 0 warning, balance tag bersih.

## Fase 3 — Verifikasi & Sync

- Pest di Docker: `docker run --rm --network container:myakad-app -v /data/projects/myakad:/app -w /app php:8.3-cli sh -c 'apt-get update -qq >/dev/null 2>&1; apt-get install -y -qq libzip-dev >/dev/null 2>&1; docker-php-ext-install pdo_mysql zip >/dev/null 2>&1; php vendor/bin/pest --compact'`
- Pint: `docker run --rm -v /data/projects/myakad:/app -w /app php:8.3-cli php vendor/bin/pint --dirty`
- Prettier: `npx prettier --write resources/`
- Docker sync: `docker cp` file PHP + Editor.vue → `migrate --force` → `view:clear`; template bind-mounted langsung live
- Cek live: `/i/redho-dan-yeli` + render preview tiap template

## Catatan
- Template gitignored → perubahan template tidak perlu commit; kode admin wajib commit & push
- AGENTS.md: no-errors → commit/push → docker sync
