# Betawi Heritage - Template Preset

## 🎨 Overview

**Betawi Heritage** adalah template preset yang sudah jadi dan siap pakai untuk undangan pernikahan digital dengan tema adat Betawi. Template ini menggunakan sistem styling global yang konsisten dan modern.

## ✅ Status: READY TO USE

Template ini sudah **100% siap digunakan** dengan semua fitur berikut:

### 🎯 Fitur Lengkap
- ✅ Opening screen dengan animasi gradient Betawi (merah-kuning)
- ✅ Hero section dengan countdown timer otomatis
- ✅ Profil mempelai dengan foto dan informasi keluarga
- ✅ Kisah cinta (opsional)
- ✅ Detail acara (Akad & Resepsi) dengan link Google Maps
- ✅ Galeri foto dengan lightbox
- ✅ RSVP & Guestbook dengan form interaktif
- ✅ Amplop digital (Bank Transfer, E-Wallet, QRIS)
- ✅ Footer dengan ucapan terima kasih
- ✅ Background music dengan kontrol play/pause
- ✅ Animasi scroll yang smooth
- ✅ Responsive design (mobile-first)

### 🎨 Styling System

**Warna Betawi:**
- Primary: `#dc2626` (Merah Betawi)
- Secondary: `#fbbf24` (Kuning Emas)
- Background: `#fef3c7` (Cream)
- Text: `#1f2937` (Dark Gray)

**Typography:**
- Heading: `Playfair Display` (serif, elegant)
- Body: `Inter` (sans-serif, modern)
- Script: `Dancing Script` (cursive, untuk aksen)

**Icons:**
- Font Awesome 6.5.1 (semua icon menggunakan `fas fa-*`)

**CSS System:**
- **Global CSS**: `/css/template-base.css` + `/css/template-components.css`
- **CSS Variables**: Dari template.json (--color-primary, --color-secondary, dll)
- **Global Classes**: template-opening, template-btn, template-card, template-title, dll
- **Global JS**: `/js/template-base.js`
- **NO Tailwind arbitrary values** - Pakai class global + CSS variables

## 📁 Struktur File

```
storage/app/public/templates/betawi-heritage/
├── template.json              # Konfigurasi template
├── sections/
│   ├── opening.html          # ✅ Opening screen
│   ├── hero.html             # ✅ Hero dengan countdown
│   ├── couple.html           # ✅ Profil mempelai
│   ├── story.html            # ✅ Kisah cinta
│   ├── event.html            # ✅ Detail acara
│   ├── gallery.html          # ✅ Galeri foto
│   ├── rsvp.html             # ✅ RSVP & Guestbook
│   ├── gift.html             # ✅ Amplop digital
│   └── footer.html           # ✅ Footer
```

## 🔧 Konfigurasi (template.json)

### Styling Configuration

```json
{
  "styling": {
    "colors": {
      "primary": "#dc2626",
      "secondary": "#fbbf24",
      "accent": "#f59e0b",
      "background": "#fef3c7",
      "text": "#1f2937",
      "text-light": "#6b7280",
      "border": "#fde68a"
    },
    "fonts": {
      "heading": "'Playfair Display', serif",
      "body": "'Inter', sans-serif",
      "script": "'Dancing Script', cursive"
    }
  }
}
```

### Features Configuration

```json
{
  "features": {
    "countdown": {
      "enabled": true,
      "selector": "#countdown"
    },
    "music": {
      "enabled": true,
      "audioSelector": "#background-music",
      "buttonSelector": "#music-toggle",
      "autoPlay": false
    },
    "opening": {
      "enabled": true,
      "screenSelector": "#opening-screen",
      "buttonSelector": "#open-invitation"
    },
    "gallery": {
      "enabled": true,
      "itemSelector": ".gallery-item"
    },
    "animations": {
      "enabled": true
    }
  }
}
```

## 📝 Data Variables

Template ini menggunakan 40+ variabel Blade yang bisa diisi dari database:

### Mempelai
- `$bride_name` - Nama mempelai wanita
- `$bride_father` - Nama ayah mempelai wanita
- `$bride_mother` - Nama ibu mempelai wanita
- `$bride_photo_url` - URL foto mempelai wanita
- `$groom_name` - Nama mempelai pria
- `$groom_father` - Nama ayah mempelai pria
- `$groom_mother` - Nama ibu mempelai pria
- `$groom_photo_url` - URL foto mempelai pria

### Acara
- `$akad_datetime` - Tanggal & waktu akad (ISO format)
- `$akad_datetime_formatted` - Tanggal akad (formatted)
- `$akad_time` - Waktu akad
- `$akad_venue` - Lokasi akad
- `$akad_maps_url` - Link Google Maps akad
- `$reception_datetime_formatted` - Tanggal resepsi
- `$reception_time` - Waktu resepsi
- `$reception_venue` - Lokasi resepsi
- `$reception_maps_url` - Link Google Maps resepsi

### Media
- `$cover_photo_url` - Foto cover hero section
- `$music_url` - URL file musik background
- `$gallery_photos` - Array foto galeri
- `$qris_image_url` - QR Code QRIS

### Amplop Digital
- `$bank_name` - Nama bank
- `$account_number` - Nomor rekening
- `$account_name` - Nama pemilik rekening
- `$gopay_number` - Nomor GoPay
- `$ovo_number` - Nomor OVO
- `$dana_number` - Nomor DANA

### Lainnya
- `$guest_name` - Nama tamu undangan
- `$love_story` - Kisah cinta (teks panjang)
- `$event_date` - Tanggal acara (display)
- `$rsvp_action` - URL endpoint RSVP form
- `$csrf_token` - CSRF token untuk form

## 🎯 Cara Menggunakan

### 1. Upload Template
```bash
# Template sudah ada di:
storage/app/public/templates/betawi-heritage/
```

### 2. Akses Preview
```
http://myakad.test/templates/betawi-heritage/preview
```

### 3. Render untuk Tamu
```
http://myakad.test/templates/betawi-heritage/render
```

### 4. Customize Data
Edit data di database atau controller untuk mengisi variabel Blade.

## 🎨 Design Pattern

### Card Style (Konsisten di semua section)
```html
<div class="template-card">
  <!-- Content -->
</div>
```

### Button Style
```html
<button class="template-btn template-btn-lg">
  <i class="fas fa-icon"></i>
  Text
</button>
```

### Section Title
```html
<div class="template-section">
  <div class="template-container">
    <div class="template-text-center template-mb-8">
      <h2 class="template-title-lg" style="font-family: var(--font-heading);">
        Title
      </h2>
      <div class="template-divider"></div>
    </div>
  </div>
</div>
```

### Input Style
```html
<input 
  type="text" 
  class="template-input"
  placeholder="Masukkan teks"
>
```

**Note**: Semua styling menggunakan **Global CSS classes** dari `template-components.css`, bukan Tailwind arbitrary values.

## 🚀 JavaScript Functions

Template menggunakan global JavaScript dari `/js/template-base.js`:

### MyAkad Namespace
```javascript
// Countdown timer
MyAkad.countdown('#countdown', '2025-06-14T09:00:00');

// Music player
MyAkad.musicPlayer('#background-music', '#music-toggle');

// Opening screen
MyAkad.openingScreen('#opening-screen', '#open-invitation');

// Gallery lightbox
MyAkad.gallery('.gallery-item');

// Copy to clipboard
MyAkad.copyToClipboard('.copy-button');

// Animate on scroll
MyAkad.animateOnScroll('.animate-on-scroll');
```

## ✨ Keunggulan Preset Ini

1. **Konsisten** - Semua section menggunakan Global CSS classes yang sama
2. **Modern** - CSS Variables + Font Awesome terbaru
3. **Responsive** - Mobile-first design dengan class global
4. **Performant** - Global CSS/JS, tidak ada duplikasi, tidak perlu compile Tailwind
5. **Maintainable** - Mudah diupdate, cukup ubah CSS variables di template.json
6. **Documented** - Semua variabel dan fungsi terdokumentasi
7. **Tested** - Sudah ditest dan berfungsi dengan baik
8. **No Build Step** - Tidak perlu npm run build, langsung pakai

## 🎯 Next Steps

### Untuk Developer:
1. ✅ Template sudah siap digunakan
2. ✅ Semua section sudah konsisten
3. ✅ Global CSS/JS sudah berfungsi
4. ✅ Preview system sudah working
5. ⏭️ Tinggal isi data dari database

### Untuk User:
1. Pilih template "Betawi Heritage"
2. Isi data mempelai & acara
3. Upload foto
4. Customize warna (opsional)
5. Publish & share link

## 📞 Support

Jika ada masalah atau pertanyaan:
- Cek dokumentasi di `docs/templates/`
- Lihat contoh di `storage/app/public/templates/betawi-heritage/`
- Test preview di `http://myakad.test/templates/betawi-heritage/preview`

---

**Status:** ✅ PRODUCTION READY  
**Version:** 1.0.0  
**Last Updated:** May 11, 2026  
**Author:** MyAkad Team
