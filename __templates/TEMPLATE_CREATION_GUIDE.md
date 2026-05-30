# Template Creation Guide - MyAkad

Panduan lengkap untuk membuat template undangan digital MyAkad di luar project yang siap di-upload sebagai ZIP file.

## 📋 Table of Contents

1. [Quick Start](#quick-start)
2. [Directory Structure](#directory-structure)
3. [Required Files](#required-files)
4. [Data Contract](#data-contract)
5. [Blade Syntax](#blade-syntax)
6. [Template.json Schema](#templatejson-schema)
7. [Creating ZIP File](#creating-zip-file)
8. [Validation Rules](#validation-rules)
9. [Testing Template](#testing-template)
10. [Example Template](#example-template)

---

## Quick Start

### Minimal Template Structure

```
my-template/
├── template.json          # ✅ REQUIRED - Metadata
├── thumbnail.jpg          # ⚠️ Optional but recommended
├── assets/
│   ├── style.css         # ✅ REQUIRED - Custom styles
│   └── script.js         # ⚠️ Optional - Custom JavaScript
├── sections/
│   ├── hero.html         # ✅ At least 1 section required
│   ├── story.html
│   ├── event.html
│   ├── gallery.html
│   ├── gift.html
│   └── rsvp.html
└── ornaments/            # ⚠️ Optional
    ├── flower-top.html
    └── divider.html
```

### 5-Minute Template

1. Create folder: `my-template/`
2. Create `template.json` with minimal config
3. Create `assets/style.css` (can be empty)
4. Create `sections/hero.html` with basic HTML
5. ZIP the folder
6. Upload via admin panel

---

## Directory Structure

### Root Files

#### `template.json` ✅ REQUIRED
Metadata dan konfigurasi template.

#### `thumbnail.jpg` ⚠️ Recommended
- Size: 800x600px atau 16:9 ratio
- Format: JPG, PNG, WebP
- Max size: 500KB
- Digunakan untuk preview di gallery

### `assets/` Directory ✅ REQUIRED

#### `assets/style.css` ✅ REQUIRED
Custom CSS untuk template. Bisa kosong tapi file harus ada.

```css
/* Minimal style.css */
/* Template akan menggunakan Tailwind CSS by default */

/* Custom animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.fade-in {
  animation: fadeIn 1s ease-in;
}
```

#### `assets/script.js` ⚠️ Optional
Custom JavaScript untuk interaktivitas.

```javascript
// Countdown timer example
document.addEventListener('DOMContentLoaded', function() {
  const eventDate = new Date('{{ $event_date ?? "" }}').getTime();
  
  function updateCountdown() {
    const now = new Date().getTime();
    const distance = eventDate - now;
    
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    document.getElementById('countdown-days').textContent = days;
    document.getElementById('countdown-hours').textContent = hours;
    document.getElementById('countdown-minutes').textContent = minutes;
    document.getElementById('countdown-seconds').textContent = seconds;
  }
  
  setInterval(updateCountdown, 1000);
  updateCountdown();
});
```

### `sections/` Directory ✅ REQUIRED

Minimal 1 section file harus ada. Setiap section adalah file HTML dengan Blade syntax.

**Common Sections**:
- `hero.html` - Opening/cover section
- `couple.html` - Bride & groom profiles
- `story.html` - Love story
- `event.html` - Event details (akad & reception)
- `gallery.html` - Photo gallery
- `video.html` - Video embed
- `rsvp.html` - RSVP form
- `gift.html` - Gift/payment info
- `footer.html` - Closing message

### `ornaments/` Directory ⚠️ Optional

Decorative elements yang bisa di-toggle on/off oleh user.

**Common Ornaments**:
- `flower-top.html` - Top decoration
- `flower-bottom.html` - Bottom decoration
- `divider.html` - Section divider
- `corner-left.html` - Left corner decoration
- `corner-right.html` - Right corner decoration

---

## Required Files

### ✅ Must Have (Validation akan gagal jika tidak ada)

1. **template.json** - Metadata file
2. **assets/style.css** - CSS file (bisa kosong)
3. **At least 1 section file** - Sesuai yang didefinisikan di `template.json`

### ⚠️ Should Have (Recommended)

1. **thumbnail.jpg** - Preview image
2. **assets/script.js** - JavaScript untuk interaktivitas
3. **Multiple sections** - Untuk template yang lengkap

### ❌ Security Rules

**TIDAK BOLEH ADA**:
- Path traversal: `../`, `../../`, `/etc/`
- Absolute paths: `/var/`, `C:\`
- Binary files di sections/ornaments (harus text/HTML)
- Executable files: `.exe`, `.sh`, `.bat`

---

## Data Contract

Semua variable yang tersedia untuk digunakan di template:

### 👰 Bride & Groom

```php
$bride_name          // string - "Siti Nurhaliza"
$bride_father        // string - "Bapak Ahmad"
$bride_mother        // string - "Ibu Aminah"
$bride_photo_url     // string|null - "https://..."

$groom_name          // string - "Budi Santoso"
$groom_father        // string - "Bapak Santoso"
$groom_mother        // string - "Ibu Dewi"
$groom_photo_url     // string|null - "https://..."
```

### 📅 Event Details

```php
// Event date for countdown (ISO 8601)
$event_date          // string - "2025-06-14T13:00:00+07:00"

// Akad Nikah
$akad_datetime_formatted  // string - "Sabtu, 14 Juni 2025"
$akad_time                // string - "09:00 WIB"
$akad_date                // string - "14"
$akad_month               // string - "Juni"
$akad_year                // string - "2025"
$akad_day                 // string - "Sabtu"
$akad_venue               // string - "Masjid Al-Ikhlas, Jl. Merdeka No. 123"
$akad_maps_url            // string|null - "https://maps.google.com/?q=..."

// Resepsi
$reception_datetime_formatted  // string - "Sabtu, 14 Juni 2025"
$reception_time                // string - "13:00 WIB"
$reception_date                // string - "14"
$reception_month               // string - "Juni"
$reception_year                // string - "2025"
$reception_day                 // string - "Sabtu"
$reception_venue               // string - "Gedung Serbaguna, Jl. Sudirman"
$reception_maps_url            // string|null - "https://maps.google.com/?q=..."
```

### 📸 Media

```php
$cover_photo_url     // string|null - URL foto cover
$music_url           // string|null - URL musik background
```

### 💬 Content

```php
$love_story          // string|null - Cerita cinta
$special_message     // string|null - Pesan khusus untuk tamu
```

### 💰 Payment Info

```php
$bank_name           // string|null - "Bank Mandiri"
$account_number      // string|null - "1234567890"
$account_name        // string|null - "Budi Santoso"
$qris_image_url      // string|null - URL gambar QRIS
$gopay_number        // string|null - "081234567890"
$ovo_number          // string|null - "081234567890"
$dana_number         // string|null - "081234567890"
```

### 🖼️ Gallery

```php
$gallery             // array - Array of gallery items
// Structure:
// [
//   ['url' => 'https://...', 'caption' => 'Foto 1'],
//   ['url' => 'https://...', 'caption' => 'Foto 2'],
// ]
```

### 📝 RSVP & Guest

```php
$rsvp_action         // string - URL endpoint untuk submit RSVP
$csrf_token          // string - CSRF token untuk form
$guest_name          // string|null - Nama tamu undangan
```

---

## Blade Syntax

### ✅ Correct Usage

#### 1. Variable Output
```blade
<!-- BENAR -->
<h1>{{ $bride_name ?? '' }}</h1>
<p>{{ $groom_name ?? '' }}</p>

<!-- SALAH -->
<h1>{{ $bride_name }}</h1>          <!-- Error jika null -->
<h1>{{$bride_name}}</h1>            <!-- Tidak ada spasi -->
<h1>$$bride_name</h1>               <!-- Double dollar -->
```

#### 2. Conditionals
```blade
<!-- BENAR -->
@if($love_story)
  <div class="story">
    <p>{{ $love_story ?? '' }}</p>
  </div>
@endif

@if($bank_name && $account_number)
  <div class="payment">
    <p>{{ $bank_name ?? '' }}: {{ $account_number ?? '' }}</p>
  </div>
@endif

<!-- SALAH -->
@if($$love_story)                   <!-- Double dollar -->
@if($love_story ?? false)           <!-- Tidak perlu ?? di @if -->
{{#if love_story}}                  <!-- Mustache syntax -->
```

#### 3. Loops (Gallery)
```blade
<!-- BENAR -->
@if(count($gallery ?? []) > 0)
  <div class="gallery">
    @foreach($gallery ?? [] as $item)
      <div class="gallery-item">
        <img src="{{ $item['url'] ?? '' }}" alt="{{ $item['caption'] ?? '' }}">
        @if($item['caption'] ?? false)
          <p>{{ $item['caption'] ?? '' }}</p>
        @endif
      </div>
    @endforeach
  </div>
@endif

<!-- SALAH -->
@foreach($gallery_urls as $url)     <!-- Variable tidak ada -->
{{#each gallery}}                   <!-- Mustache syntax -->
```

#### 4. Multiple Conditions
```blade
<!-- BENAR -->
@if($gopay_number || $ovo_number || $dana_number)
  <div class="ewallet">
    @if($gopay_number)
      <p>GoPay: {{ $gopay_number ?? '' }}</p>
    @endif
    @if($ovo_number)
      <p>OVO: {{ $ovo_number ?? '' }}</p>
    @endif
    @if($dana_number)
      <p>DANA: {{ $dana_number ?? '' }}</p>
    @endif
  </div>
@endif
```

#### 5. String Functions
```blade
<!-- BENAR -->
<p>{{ strtoupper($bride_name ?? '') }}</p>
<p>{{ ucfirst($groom_name ?? '') }}</p>
<p>{{ str_replace(' ', '-', $venue ?? '') }}</p>
```

---

## Template.json Schema

### Minimal Configuration

```json
{
  "name": "My Template",
  "slug": "my-template",
  "version": "1.0.0",
  "sections": [
    {
      "file": "hero.html",
      "label": "Hero Section"
    }
  ]
}
```

### Full Configuration

```json
{
  "name": "Elegant Wedding",
  "slug": "elegant-wedding",
  "version": "1.0.0",
  "thumbnail": "thumbnail.jpg",
  "is_free": false,
  "price": 149000,
  "description": "Template elegan dengan desain modern dan minimalis",
  "author": "MyAkad Team",
  "sections": [
    {
      "file": "hero.html",
      "label": "Hero Section",
      "sort_order": 1,
      "is_required": true
    },
    {
      "file": "couple.html",
      "label": "Couple Profile",
      "sort_order": 2,
      "is_required": false
    },
    {
      "file": "story.html",
      "label": "Love Story",
      "sort_order": 3,
      "is_required": false
    },
    {
      "file": "event.html",
      "label": "Event Details",
      "sort_order": 4,
      "is_required": true
    },
    {
      "file": "gallery.html",
      "label": "Photo Gallery",
      "sort_order": 5,
      "is_required": false
    },
    {
      "file": "rsvp.html",
      "label": "RSVP Form",
      "sort_order": 6,
      "is_required": true
    },
    {
      "file": "gift.html",
      "label": "Gift Info",
      "sort_order": 7,
      "is_required": false
    }
  ],
  "ornaments": [
    {
      "file": "flower-top.html",
      "label": "Top Flower",
      "position": "top",
      "default_active": true
    },
    {
      "file": "divider.html",
      "label": "Section Divider",
      "position": "between",
      "default_active": false
    }
  ],
  "variables": [
    "bride_name",
    "groom_name",
    "event_date",
    "akad_venue",
    "reception_venue",
    "gallery"
  ]
}
```

### Field Descriptions

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ Yes | Nama template (display name) |
| `slug` | string | ✅ Yes | Unique identifier (lowercase, dash-separated) |
| `version` | string | ⚠️ Recommended | Semantic version (e.g., "1.0.0") |
| `thumbnail` | string | ⚠️ Recommended | Filename of thumbnail image |
| `is_free` | boolean | ❌ No | Default: false |
| `price` | number | ❌ No | Price in IDR, default: 0 |
| `description` | string | ❌ No | Template description |
| `author` | string | ❌ No | Template author/creator |
| `sections` | array | ✅ Yes | List of section files |
| `ornaments` | array | ❌ No | List of ornament files |
| `variables` | array | ❌ No | List of used variables (for documentation) |

### Sections Array

**Simple format** (string array):
```json
"sections": ["hero", "story", "gallery"]
```
System akan otomatis append `.html` dan capitalize label.

**Detailed format** (object array):
```json
"sections": [
  {
    "file": "hero.html",
    "label": "Hero Section",
    "sort_order": 1,
    "is_required": true
  }
]
```

### Ornaments Array

```json
"ornaments": [
  {
    "file": "flower-top.html",
    "label": "Top Decoration",
    "position": "top",
    "default_active": true
  }
]
```

**Position values**: `top`, `bottom`, `between`, `left`, `right`

---

## Creating ZIP File

### Windows (File Explorer)

1. Select semua file/folder di dalam `my-template/`
2. Right-click → Send to → Compressed (zipped) folder
3. Rename menjadi `my-template.zip`

⚠️ **PENTING**: Jangan zip folder `my-template/` itu sendiri. Zip isi folder-nya.

**Struktur ZIP yang BENAR**:
```
my-template.zip
├── template.json
├── assets/
└── sections/
```

**Struktur ZIP yang SALAH**:
```
my-template.zip
└── my-template/
    ├── template.json
    ├── assets/
    └── sections/
```

### Windows (Command Line)

```cmd
cd my-template
tar -a -c -f ..\my-template.zip *
```

### PowerShell

```powershell
Compress-Archive -Path "my-template\*" -DestinationPath "my-template.zip"
```

### Linux/Mac

```bash
cd my-template
zip -r ../my-template.zip .
```

---

## Validation Rules

Template akan divalidasi saat upload. Berikut rules yang harus dipenuhi:

### ✅ File Structure Validation

1. **template.json must exist**
   - Error: "template.json not found in ZIP."

2. **template.json must be valid JSON**
   - Error: "template.json contains invalid JSON"

3. **Required fields in template.json**
   - `name` and `slug` must be present
   - Error: "template.json missing required fields: name, slug"

4. **assets/style.css must exist**
   - Error: "Required file assets/style.css not found."

5. **All section files must exist**
   - Error: "Missing section files: sections/hero.html, sections/story.html"

6. **All ornament files must exist** (if defined)
   - Error: "Ornament file not found: ornaments/flower.html"

### ✅ Security Validation

1. **No path traversal**
   - Tidak boleh ada: `../`, `../../`, `/etc/`, `C:\`
   - Error: "ZIP contains unsafe paths (path traversal detected)."

2. **No absolute paths**
   - Tidak boleh ada: `/var/`, `/home/`, `C:\Windows\`
   - Error: "ZIP contains unsafe paths (path traversal detected)."

3. **Text files only in sections/ornaments**
   - Binary files tidak diperbolehkan
   - Error: "Section file contains binary content"

### ✅ Content Validation

1. **Valid HTML in sections**
   - Harus valid HTML/Blade syntax
   - Tidak boleh ada null bytes atau binary content

2. **Valid CSS in assets/style.css**
   - Harus valid CSS syntax

3. **Valid JavaScript in assets/script.js** (if exists)
   - Harus valid JavaScript syntax

---

## Testing Template

### 1. Upload via Admin Panel

1. Login ke admin panel: `http://myakad.test/admin`
2. Navigate to Templates
3. Click "Upload Template" button
4. Select your ZIP file
5. Click "Upload"

### 2. Check Upload Result

**Success**:
```
✅ Template 'My Template' uploaded successfully.
```

**Failure**:
```
❌ Template validation failed: template.json not found in ZIP.
```

### 3. Sync Templates (Alternative)

Jika upload gagal, bisa extract manual ke `storage/templates/` dan sync:

```bash
php artisan templates:sync
```

### 4. Preview Template

1. Navigate to: `http://myakad.test/templates`
2. Find your template
3. Click "Preview"
4. Check semua sections tampil dengan benar

### 5. Test with Dummy Data

Preview akan menggunakan dummy data. Pastikan:
- ✅ Semua sections tampil
- ✅ Tidak ada error di browser console
- ✅ Responsive di mobile & desktop
- ✅ Images load correctly
- ✅ Countdown timer berfungsi (jika ada)
- ✅ Forms berfungsi (jika ada)

---

## Example Template

### Minimal Working Template

#### `template.json`
```json
{
  "name": "Simple Wedding",
  "slug": "simple-wedding",
  "version": "1.0.0",
  "sections": [
    {
      "file": "hero.html",
      "label": "Hero"
    }
  ]
}
```

#### `assets/style.css`
```css
/* Minimal styles */
body {
  font-family: 'Georgia', serif;
}

.hero {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-align: center;
  padding: 2rem;
}

.hero h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.hero p {
  font-size: 1.5rem;
  opacity: 0.9;
}
```

#### `sections/hero.html`
```blade
<div class="hero">
  <div class="hero-content">
    <p style="font-size: 1rem; letter-spacing: 0.2em; margin-bottom: 1rem;">THE WEDDING OF</p>
    <h1>{{ $groom_name ?? '' }} & {{ $bride_name ?? '' }}</h1>
    <p>{{ $reception_datetime_formatted ?? '' }}</p>
    
    @if($reception_venue)
      <p style="margin-top: 2rem; font-size: 1.2rem;">
        {{ $reception_venue ?? '' }}
      </p>
    @endif
  </div>
</div>
```

### ZIP this template:
```
simple-wedding/
├── template.json
├── assets/
│   └── style.css
└── sections/
    └── hero.html
```

Compress menjadi `simple-wedding.zip` dan upload!

---

## Troubleshooting

### Error: "template.json not found in ZIP"

**Cause**: ZIP structure salah, ada parent folder.

**Solution**: Zip isi folder, bukan folder itu sendiri.

```bash
# SALAH
zip -r template.zip my-template/

# BENAR
cd my-template
zip -r ../template.zip .
```

### Error: "Required file assets/style.css not found"

**Cause**: File `assets/style.css` tidak ada.

**Solution**: Buat file `assets/style.css` (bisa kosong).

```bash
mkdir assets
touch assets/style.css
```

### Error: "Missing section files: sections/hero.html"

**Cause**: File section yang didefinisikan di `template.json` tidak ada.

**Solution**: Buat file section atau update `template.json`.

### Error: "ZIP contains unsafe paths"

**Cause**: Ada path traversal (`../`) atau absolute path (`/var/`).

**Solution**: Pastikan semua path relative dan tidak ada `../`.

### Preview tidak tampil / blank

**Cause**: 
- Syntax error di Blade
- Missing closing tags
- JavaScript error

**Solution**: 
- Check browser console untuk errors
- Validate HTML syntax
- Test JavaScript di console

---

## Best Practices

### 1. Use Global CSS Classes

Template system menggunakan Global CSS dari `template-components.css`. **JANGAN pakai Tailwind arbitrary values**.

```html
<!-- BENAR - Pakai Global CSS classes -->
<div class="template-container">
  <h1 class="template-title-lg template-text-center template-mb-4" style="font-family: var(--font-heading);">
    {{ $bride_name ?? '' }} & {{ $groom_name ?? '' }}
  </h1>
</div>

<!-- SALAH - Jangan pakai Tailwind arbitrary values -->
<div class="container mx-auto px-4">
  <h1 class="text-4xl font-bold text-center mb-8 text-[#dc2626]">
    {{ $bride_name ?? '' }} & {{ $groom_name ?? '' }}
  </h1>
</div>
```

**Available Global Classes:**
- Layout: `template-section`, `template-container`, `template-opening`
- Typography: `template-title`, `template-title-lg`, `template-subtitle`, `template-text`
- Colors: `template-text-white`, `template-text-primary`, `template-text-secondary`
- Components: `template-btn`, `template-card`, `template-divider`
- Spacing: `template-mb-1` sampai `template-mb-8`, `template-mt-1` sampai `template-mt-8`
- Grid: `template-grid-2`, `template-grid-3`, `template-grid-4`

**CSS Variables (dari template.json):**
- `var(--color-primary)` - Warna utama
- `var(--color-secondary)` - Warna aksen
- `var(--color-accent)` - Warna tambahan
- `var(--color-background)` - Warna background
- `var(--color-text)` - Warna text
- `var(--font-heading)` - Font untuk heading
- `var(--font-body)` - Font untuk body text

### 2. Mobile-First Design

Gunakan responsive classes dari Global CSS:

```html
<!-- Grid responsive -->
<div class="template-grid-2">
  <!-- 1 column mobile, 2 columns desktop -->
</div>

<!-- Typography responsive -->
<h1 class="template-title-lg">
  <!-- Font size otomatis responsive -->
</h1>

<!-- Section spacing responsive -->
<section class="template-section">
  <!-- Padding otomatis responsive (3rem mobile, 5rem desktop) -->
</section>
```

### 3. Always Use Null Coalescing

```blade
<!-- BENAR -->
{{ $variable ?? '' }}
{{ $variable ?? 'default value' }}

<!-- SALAH -->
{{ $variable }}
```

### 4. Check Before Loop

```blade
@if(count($gallery ?? []) > 0)
  @foreach($gallery ?? [] as $item)
    <!-- Content -->
  @endforeach
@else
  <p>No photos available</p>
@endif
```

### 5. Optimize Images

- Compress images before adding to template
- Use appropriate image formats (WebP for photos, SVG for icons)
- Lazy load images: `loading="lazy"`

### 6. Test Thoroughly

- Test di berbagai screen sizes
- Test dengan data kosong (null values)
- Test dengan data lengkap
- Check browser console untuk errors

---

## Resources

- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Blade Templates Documentation](https://laravel.com/docs/blade)
- [Material Icons](https://fonts.google.com/icons)
- [Google Fonts](https://fonts.google.com/)

---

## Support

Untuk pertanyaan atau issue:
1. Check dokumentasi ini
2. Review example templates di `storage/templates/`
3. Contact tim development MyAkad

---

**Happy Template Creating! 🎨**
