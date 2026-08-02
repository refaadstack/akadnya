# Prompt untuk Generate Template MyAkad

## 🎯 Prompt Template Generator

Gunakan prompt ini untuk generate template undangan pernikahan baru dengan AI (Claude, ChatGPT, dll).

---

## 📋 Prompt Lengkap

```
Buatkan template undangan pernikahan digital untuk MyAkad dengan tema [TEMA].

## Context
MyAkad adalah platform undangan digital pernikahan yang menggunakan:
- Global CSS system dari template-components.css dan template-base.css
- CSS Variables dari template.json untuk theming (--color-primary, --color-secondary, dll)
- Blade syntax untuk dynamic data
- Font Awesome 6.5.1 untuk icons
- Mobile-first responsive design

## Requirements

### 1. template.json
Buat file template.json dengan:
- name: "[NAMA TEMPLATE]"
- slug: "[slug-template]"
- version: "1.0.0"
- description: "[Deskripsi template]"
- is_free: false
- price: [HARGA]
- styling:
  - colors: primary, secondary, accent, background, text, text-light, border (sesuai tema [TEMA])
  - fonts: heading, body, script (pilih font yang cocok dengan tema)
- features: countdown, music, opening, gallery, animations (semua enabled: true)
- sections: array dengan 9 sections (opening, hero, couple, story, event, gallery, rsvp, gift, footer)

### 2. Section Files (9 files)
Buat 9 file HTML dengan Blade syntax di folder sections/:

#### opening.html
- Opening screen dengan background gradient (var(--color-primary) to var(--color-accent))
- SVG ornament sesuai tema [TEMA]
- Guest name: {{ $guest_name ?? 'Bapak/Ibu/Saudara/i' }}
- Couple names: {{ $groom_name ?? '' }} & {{ $bride_name ?? '' }}
- Button "Buka Undangan" dengan class: template-btn template-btn-lg
- Music control button dengan class: template-music-btn
- Background music: <audio id="background-music" loop>

#### hero.html
- Hero section dengan background image: {{ $cover_photo_url ?? '' }}
- Bismillah text (Arabic + transliteration)
- Couple names dengan ampersand
- Event date: {{ $reception_datetime_formatted ?? '' }}
- Venue: {{ $reception_venue ?? '' }}
- Countdown timer dengan id="countdown" dan data-event-date="{{ $event_date ?? '' }}"
- 4 countdown boxes (Hari, Jam, Menit, Detik) dengan class: template-countdown-item
- Scroll indicator (chevron down icon)

#### couple.html
- Section title "Mempelai" dengan class: template-title-lg
- Divider dengan class: template-divider
- Grid 2 columns dengan class: template-grid-2
- Groom card:
  - Photo: {{ $groom_photo_url ?? 'placeholder' }}
  - Name: {{ $groom_name ?? '' }}
  - Parents: {{ $groom_father ?? '' }} & {{ $groom_mother ?? '' }}
- Bride card (sama seperti groom)
- Quran verse (QS. Ar-Rum: 21) dengan Arabic text + translation

#### story.html
- Section title "Kisah Cinta Kami"
- Card dengan class: template-card
- Heart icon (fas fa-heart)
- Love story text: {{ $love_story ?? 'Default story text' }}

#### event.html
- Section title "Waktu & Tempat"
- Grid 2 columns untuk Akad & Resepsi
- Akad card:
  - Icon: fas fa-mosque
  - Date: {{ $akad_datetime_formatted ?? '' }}
  - Time: {{ $akad_time ?? '' }}
  - Venue: {{ $akad_venue ?? '' }}
  - Maps button: {{ $akad_maps_url ?? '#' }}
- Resepsi card (sama seperti akad)
- "Tambah ke Kalender" button

#### gallery.html
- Section title "Galeri Bahagia"
- Grid 4 columns (2 mobile, 4 desktop) dengan class: template-grid-4
- Loop gallery: @foreach($gallery ?? [] as $item)
- Gallery item dengan class: gallery-item
- Image: {{ $item['url'] ?? '' }}
- Caption: {{ $item['caption'] ?? '' }}

#### rsvp.html
- Section title "Konfirmasi Kehadiran"
- RSVP form dengan action="{{ $rsvp_action ?? '#' }}"
- CSRF token: @csrf
- Fields:
  - Name (required)
  - Attendance (select: Hadir, Tidak Hadir, Masih Ragu)
  - Guest count (number, max 5)
  - Message (textarea)
- Submit button dengan class: template-btn
- Guestbook display (sample messages dengan avatar)

#### gift.html
- Section title "Amplop Digital"
- Grid 3 columns untuk payment methods
- Bank Transfer card:
  - Icon: fas fa-university
  - Bank name: {{ $bank_name ?? '' }}
  - Account number: {{ $account_number ?? '' }}
  - Account name: {{ $account_name ?? '' }}
  - Copy button dengan class: copy-button
- E-Wallet cards (GoPay, OVO, DANA)
- QRIS card dengan QR image: {{ $qris_image_url ?? '' }}

#### footer.html
- Background gradient (var(--color-primary) to var(--color-accent))
- Heart icon (fas fa-heart)
- Thank you message
- Couple names
- Copyright text
- MyAkad branding

### 3. CSS Classes yang HARUS Digunakan

**Layout:**
- template-section (untuk section wrapper)
- template-container (untuk content container)
- template-opening (untuk opening screen)
- template-opening-content (untuk opening content)

**Typography:**
- template-title (heading 2)
- template-title-lg (heading 1)
- template-subtitle (small uppercase text)
- template-text (body text)
- template-text-center (centered text)

**Colors:**
- template-text-white
- template-text-primary
- template-text-secondary
- template-bg-primary
- template-bg-secondary
- template-bg-gradient

**Components:**
- template-btn (button)
- template-btn-lg (large button)
- template-btn-primary (primary color button)
- template-card (card container)
- template-card-image (card image wrapper)
- template-divider (section divider)
- template-countdown (countdown container)
- template-countdown-item (countdown box)
- template-music-btn (music control button)

**Grid:**
- template-grid-2 (2 columns desktop, 1 mobile)
- template-grid-3 (3 columns desktop, 1 mobile)
- template-grid-4 (4 columns desktop, 2 mobile)

**Spacing:**
- template-mb-1, template-mb-2, template-mb-3, template-mb-4, template-mb-6, template-mb-8
- template-mt-1, template-mt-2, template-mt-3, template-mt-4, template-mt-6, template-mt-8

### 4. CSS Variables yang HARUS Digunakan

Untuk warna, SELALU gunakan CSS Variables:
- `var(--color-primary)` - Warna utama
- `var(--color-secondary)` - Warna aksen
- `var(--color-accent)` - Warna tambahan
- `var(--color-background)` - Background
- `var(--color-text)` - Text color

Untuk font:
- `style="font-family: var(--font-heading);"` - Untuk heading
- `style="font-family: var(--font-body);"` - Untuk body (optional, sudah default)

### 5. Blade Syntax Rules

**SELALU gunakan null coalescing:**
```blade
{{ $variable ?? '' }}
{{ $variable ?? 'default value' }}
```

**Conditionals:**
```blade
@if($variable)
  <div>{{ $variable ?? '' }}</div>
@endif
```

**Loops:**
```blade
@foreach($gallery ?? [] as $item)
  <img src="{{ $item['url'] ?? '' }}">
@endforeach
```

### 6. Icons (Font Awesome 6.5.1)

Gunakan Font Awesome icons yang sesuai tema:
- fas fa-envelope-open (opening)
- fas fa-music (music control)
- fas fa-heart (love/footer)
- fas fa-mosque (akad)
- fas fa-glass-cheers (resepsi)
- fas fa-map-marker-alt (maps)
- fas fa-calendar-plus (calendar)
- fas fa-university (bank)
- fas fa-wallet (e-wallet)
- fas fa-qrcode (QRIS)
- fas fa-paper-plane (submit)
- fas fa-copy (copy button)

### 7. Ornament (Optional)

Jika tema [TEMA] punya ornamen khas (batik, songket, ukiran, dll), buat file ornaments/divider.html dengan SVG ornament yang sesuai.

## Output Format

Berikan output dalam format:

1. **template.json** (JSON lengkap)
2. **sections/opening.html** (HTML lengkap)
3. **sections/hero.html** (HTML lengkap)
4. **sections/couple.html** (HTML lengkap)
5. **sections/story.html** (HTML lengkap)
6. **sections/event.html** (HTML lengkap)
7. **sections/gallery.html** (HTML lengkap)
8. **sections/rsvp.html** (HTML lengkap)
9. **sections/gift.html** (HTML lengkap)
10. **sections/footer.html** (HTML lengkap)
11. **ornaments/divider.html** (jika ada)

## Tema: [TEMA]

[Jelaskan tema secara detail: warna, budaya, ornamen, nuansa, dll]

Contoh:
- Jawa Klasik: Coklat (#8B4513), emas (#FFD700), batik pattern, wayang ornament, elegant & traditional
- Sunda Modern: Hijau (#2E7D32), putih (#FFFFFF), minimalis, clean, modern
- Minang Elegan: Merah (#DC143C), emas (#FFD700), rumah gadang ornament, royal & elegant
```

---

## 🎯 Contoh Penggunaan

### Prompt untuk Generate Template "Jawa Klasik"

```
Buatkan template undangan pernikahan digital untuk MyAkad dengan tema Jawa Klasik.

[... copy semua context dan requirements dari atas ...]

## Tema: Jawa Klasik

Tema Jawa Klasik dengan karakteristik:
- **Warna Utama**: Coklat tua (#8B4513) untuk primary, emas (#FFD700) untuk secondary
- **Warna Tambahan**: Coklat muda (#D2691E) untuk accent, cream (#FFF8DC) untuk background
- **Font**: Playfair Display untuk heading (elegant serif), Crimson Text untuk body
- **Ornamen**: Batik pattern, wayang silhouette, gunungan
- **Nuansa**: Traditional, elegant, royal, cultural
- **Icon Style**: Gunakan icon yang sesuai budaya Jawa (mosque untuk akad, traditional building untuk resepsi)
- **SVG Ornament**: Buat SVG wayang atau batik pattern untuk opening screen dan divider

Color Scheme:
```json
"colors": {
  "primary": "#8B4513",
  "secondary": "#FFD700",
  "accent": "#D2691E",
  "background": "#FFF8DC",
  "text": "#3E2723",
  "text-light": "#6D4C41",
  "border": "#FFD700"
}
```

Font Pairing:
```json
"fonts": {
  "heading": "'Playfair Display', serif",
  "body": "'Crimson Text', serif",
  "script": "'Great Vibes', cursive"
}
```
```

---

## 📝 Template untuk Tema Lain

Ganti bagian **Tema** dengan salah satu dari:

### Sunda Modern
- Primary: #2E7D32 (hijau), Secondary: #FFFFFF (putih)
- Accent: #81C784 (hijau muda), Background: #F1F8E9
- Font: Montserrat (heading), Open Sans (body)
- Ornamen: Minimalis, geometric pattern, angklung silhouette
- Nuansa: Modern, clean, fresh, natural

### Minang Elegan
- Primary: #DC143C (merah), Secondary: #FFD700 (emas)
- Accent: #8B0000 (merah tua), Background: #FFF5E1
- Font: Cormorant Garamond (heading), Lora (body)
- Ornamen: Rumah gadang, gonjong roof, songket pattern
- Nuansa: Royal, elegant, traditional, majestic

### Betawi Heritage
- Primary: #DC2626 (merah), Secondary: #FBBF24 (kuning)
- Accent: #F59E0B (orange), Background: #FEF3C7
- Font: Playfair Display (heading), Inter (body)
- Ornamen: Ondel-ondel, betawi pattern
- Nuansa: Colorful, festive, cultural, cheerful

### Bali Exotic
- Primary: #7B1FA2 (ungu), Secondary: #FFD700 (emas)
- Accent: #9C27B0 (ungu muda), Background: #F3E5F5
- Font: Cinzel (heading), Lato (body)
- Ornamen: Pura gate, frangipani flower, balinese pattern
- Nuansa: Exotic, spiritual, elegant, tropical

### Aceh Traditional
- Primary: #1B5E20 (hijau tua), Secondary: #FFD700 (emas)
- Accent: #388E3C (hijau), Background: #F1F8E9
- Font: Merriweather (heading), Nunito (body)
- Ornamen: Masjid Raya, aceh pattern, rencong
- Nuansa: Islamic, traditional, elegant, cultural

### Bugis Royal
- Primary: #0D47A1 (biru), Secondary: #FFD700 (emas)
- Accent: #1976D2 (biru muda), Background: #E3F2FD
- Font: Libre Baskerville (heading), Source Sans Pro (body)
- Ornamen: Perahu pinisi, bugis pattern
- Nuansa: Royal, maritime, elegant, majestic

### Dayak Ethnic
- Primary: #5D4037 (coklat), Secondary: #D32F2F (merah)
- Accent: #8D6E63 (coklat muda), Background: #EFEBE9
- Font: Alegreya (heading), Roboto (body)
- Ornamen: Mandau, dayak pattern, hornbill
- Nuansa: Ethnic, tribal, bold, cultural

### Batak Toba
- Primary: #C62828 (merah), Secondary: #212121 (hitam)
- Accent: #E53935 (merah muda), Background: #FAFAFA
- Font: Spectral (heading), Karla (body)
- Ornamen: Ulos pattern, batak house, gorga
- Nuansa: Bold, traditional, strong, cultural

### Madura Colorful
- Primary: #D32F2F (merah), Secondary: #FBC02D (kuning)
- Accent: #F57C00 (orange), Background: #FFF9C4
- Font: Poppins (heading), Quicksand (body)
- Ornamen: Karapan sapi, madura pattern
- Nuansa: Colorful, festive, energetic, cheerful

---

## ✅ Checklist Output

Pastikan output yang dihasilkan memiliki:
- [ ] template.json dengan color scheme dan fonts yang sesuai tema
- [ ] 9 section files (opening, hero, couple, story, event, gallery, rsvp, gift, footer)
- [ ] Semua menggunakan Global CSS classes (template-*)
- [ ] Semua warna menggunakan CSS Variables (var(--color-*))
- [ ] Semua variable Blade menggunakan null coalescing ({{ $var ?? '' }})
- [ ] Font Awesome icons yang sesuai
- [ ] SVG ornament yang sesuai tema (jika ada)
- [ ] Mobile-first responsive design
- [ ] Consistent styling pattern

---

**Tips**: Copy prompt ini, ganti [TEMA] dengan tema yang diinginkan, dan paste ke AI assistant (Claude, ChatGPT, dll) untuk generate template lengkap!
