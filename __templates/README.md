# MyAkad Template Documentation

Dokumentasi lengkap untuk membuat template undangan digital MyAkad.

## 📚 Files

1. **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** - Quick reference card untuk membuat template minimal
2. **[TEMPLATE_CREATION_GUIDE.md](TEMPLATE_CREATION_GUIDE.md)** - Panduan lengkap membuat template dari nol
3. **[BETAWI_HERITAGE_PRESET.md](BETAWI_HERITAGE_PRESET.md)** - Contoh template lengkap yang siap pakai
4. **[STYLING_PATTERNS.md](STYLING_PATTERNS.md)** - Pattern styling yang konsisten untuk semua template

## 🎨 Styling System

MyAkad menggunakan **Global CSS System** dengan komponen berikut:

### Global CSS Files
- `/css/template-base.css` - Base styles dan reset
- `/css/template-components.css` - Reusable component classes
- `/js/template-base.js` - Global JavaScript functions

### CSS Variables (dari template.json)
```css
:root {
  --color-primary: #dc2626;
  --color-secondary: #fbbf24;
  --color-accent: #991b1b;
  --color-background: #FFFAF0;
  --color-text: #1f2937;
  --font-heading: 'Playfair Display', serif;
  --font-body: 'Inter', sans-serif;
}
```

### Global CSS Classes
```html
<!-- Layout -->
<div class="template-section">
<div class="template-container">
<div class="template-opening">

<!-- Typography -->
<h1 class="template-title-lg">
<h2 class="template-title">
<p class="template-subtitle">
<p class="template-text">

<!-- Colors -->
<p class="template-text-white">
<p class="template-text-primary">
<p class="template-text-secondary">

<!-- Components -->
<button class="template-btn">
<div class="template-card">
<div class="template-divider">

<!-- Grid -->
<div class="template-grid-2">  <!-- 1 col mobile, 2 col desktop -->
<div class="template-grid-3">  <!-- 1 col mobile, 3 col desktop -->
<div class="template-grid-4">  <!-- 2 col mobile, 4 col desktop -->

<!-- Spacing -->
<div class="template-mb-4">    <!-- margin-bottom -->
<div class="template-mt-4">    <!-- margin-top -->
```

## ⚠️ PENTING: Jangan Pakai Tailwind Arbitrary Values

**SALAH** ❌:
```html
<div class="bg-[#dc2626] text-white px-8 py-4 rounded-full">
<button class="inline-flex items-center gap-2">
```

**BENAR** ✅:
```html
<div class="template-bg-primary template-text-white">
<button class="template-btn">
```

## 🚀 Quick Start

### 1. Minimal Template Structure
```
my-template/
├── template.json          # Metadata & config
├── assets/
│   └── style.css         # Custom CSS (bisa kosong)
└── sections/
    └── hero.html         # Minimal 1 section
```

### 2. Minimal template.json
```json
{
  "name": "My Template",
  "slug": "my-template",
  "styling": {
    "colors": {
      "primary": "#dc2626",
      "secondary": "#fbbf24",
      "accent": "#991b1b",
      "background": "#FFFAF0",
      "text": "#1f2937"
    },
    "fonts": {
      "heading": "'Playfair Display', serif",
      "body": "'Inter', sans-serif"
    }
  },
  "sections": [
    {"file": "hero.html", "label": "Hero"}
  ]
}
```

### 3. Minimal section HTML
```blade
<div class="template-opening">
  <div class="template-opening-content">
    <h1 class="template-title-lg template-text-white template-mb-4" style="font-family: var(--font-heading);">
      {{ $groom_name ?? '' }} & {{ $bride_name ?? '' }}
    </h1>
    <button class="template-btn template-btn-lg">
      <i class="fas fa-envelope-open"></i>
      Buka Undangan
    </button>
  </div>
</div>
```

## 📖 Documentation Flow

1. **Baru mulai?** → Baca [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
2. **Mau detail lengkap?** → Baca [TEMPLATE_CREATION_GUIDE.md](TEMPLATE_CREATION_GUIDE.md)
3. **Butuh contoh?** → Lihat [BETAWI_HERITAGE_PRESET.md](BETAWI_HERITAGE_PRESET.md)
4. **Mau konsisten styling?** → Ikuti [STYLING_PATTERNS.md](STYLING_PATTERNS.md)

## 🎯 Template Examples

### Existing Templates
- **Palembang Megah** - `storage/app/public/templates/palembang-megah/`
  - Tema: Adat Palembang
  - Warna: Merah emas (#8B0000, #FFD700)
  - Status: ✅ Production Ready

### Planned Templates (from documentation)
- **Betawi Heritage** - Merah kuning, ondel-ondel
- **Jawa Klasik** - Coklat emas, batik
- **Sunda Modern** - Hijau putih, minimalis
- **Minang Elegan** - Merah emas, rumah gadang
- **Bali Exotic** - Ungu emas, pura

## 🛠️ Development Tools

### Artisan Commands
```bash
# Sync templates from storage to database
php artisan templates:sync

# Validate template structure
php artisan template:validate {slug}

# Generate preview
php artisan template:preview {slug}
```

### Preview URL
```
http://myakad.test/templates/{slug}/preview
```

## 📝 Data Contract

Semua variable yang tersedia di template:

### Mempelai
- `$bride_name`, `$bride_father`, `$bride_mother`, `$bride_photo_url`
- `$groom_name`, `$groom_father`, `$groom_mother`, `$groom_photo_url`

### Acara
- `$event_date` (ISO 8601 untuk countdown)
- `$akad_datetime_formatted`, `$akad_time`, `$akad_venue`, `$akad_maps_url`
- `$reception_datetime_formatted`, `$reception_time`, `$reception_venue`, `$reception_maps_url`

### Media
- `$cover_photo_url`, `$music_url`, `$gallery` (array)

### Payment
- `$bank_name`, `$account_number`, `$account_name`
- `$qris_image_url`, `$gopay_number`, `$ovo_number`, `$dana_number`

### Lainnya
- `$love_story`, `$special_message`, `$guest_name`
- `$rsvp_action`, `$csrf_token`

## ✅ Validation Checklist

- [ ] `template.json` exists with `name`, `slug`, and `styling`
- [ ] `assets/style.css` exists (bisa kosong)
- [ ] All section files exist
- [ ] No Tailwind arbitrary values (use Global CSS classes)
- [ ] All variables use `{{ $var ?? '' }}`
- [ ] CSS Variables used for colors: `var(--color-primary)`
- [ ] Responsive design (mobile-first)
- [ ] Tested in preview

## 🐛 Common Issues

| Issue | Solution |
|-------|----------|
| Template tidak tampil | Cek browser console, pastikan Global CSS loaded |
| Warna tidak muncul | Pastikan CSS Variables defined di template.json |
| Class tidak work | Pakai Global CSS classes, bukan Tailwind arbitrary |
| Preview blank | Cek Blade syntax, pastikan semua `{{ $var ?? '' }}` |

## 📞 Support

- Check existing templates: `storage/app/public/templates/`
- Read full guide: [TEMPLATE_CREATION_GUIDE.md](TEMPLATE_CREATION_GUIDE.md)
- See patterns: [STYLING_PATTERNS.md](STYLING_PATTERNS.md)

---

**Last Updated:** May 11, 2026  
**System Version:** MyAkad v2.0  
**CSS System:** Global CSS + CSS Variables (NO Tailwind arbitrary values)
