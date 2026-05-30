# Template Creation Guide - MyAkad

Panduan lengkap untuk membuat template undangan digital MyAkad di luar project yang siap di-upload sebagai ZIP file.

> **🎨 NEW SYSTEM**: Template sekarang menggunakan **Global CSS/JS** yang dikontrol via `template.json`. Tidak perlu lagi membuat file CSS/JS custom per template!

## 📋 Table of Contents

1. [Quick Start](#quick-start)
2. [Directory Structure](#directory-structure)
3. [Required Files](#required-files)
4. [Data Contract](#data-contract)
5. [Blade Syntax](#blade-syntax)
6. [Template.json Schema](#templatejson-schema)
7. [Styling System](#styling-system)
8. [Features Configuration](#features-configuration)
9. [Creating ZIP File](#creating-zip-file)
10. [Validation Rules](#validation-rules)
11. [Testing Template](#testing-template)
12. [Example Template](#example-template)

---

## Quick Start

### Minimal Template Structure

```
my-template/
├── template.json          # ✅ REQUIRED - Metadata & Configuration
├── thumbnail.jpg          # ⚠️ Optional but recommended
└── sections/
    ├── hero.html         # ✅ At least 1 section required
    ├── story.html
    ├── event.html
    ├── gallery.html
    ├── gift.html
    └── rsvp.html
```

**🎉 That's it!** No CSS/JS files needed. Styling dikontrol via `template.json`.

### 3-Minute Template

1. Create folder: `my-template/`
2. Create `template.json` with config (see example below)
3. Create `sections/hero.html` with Tailwind CSS classes
4. ZIP the folder
5. Upload via admin panel

---

## Directory Structure

### Root Files

#### `template.json` ✅ REQUIRED
Metadata, konfigurasi styling, dan features template.

**Example:**
```json
{
  "slug": "elegant-rose",
  "name": "Elegant Rose",
  "description": "Template elegan dengan nuansa bunga mawar",
  "version": "1.0.0",
  "sections": [
    {"file": "hero.html", "label": "Hero Section"},
    {"file": "story.html", "label": "Love Story"}
  ],
  "styling": {
    "colors": {
      "primary": "#dc2626",
      "secondary": "#fbbf24"
    },
    "fonts": {
      "heading": "Playfair Display",
      "body": "Inter"
    }
  },
  "features": {
    "countdown": true,
    "music": true
  }
}
```

#### `thumbnail.jpg` ⚠️ Recommended
- Size: 800x600px atau 16:9 ratio
- Format: JPG, PNG, WebP
- Max size: 500KB
- Digunakan untuk preview di gallery

### `sections/` Directory ✅ REQUIRED

Folder berisi file HTML untuk setiap section template. Minimal 1 section required.

**Naming Convention:**
- `hero.html` - Hero/cover section
- `couple.html` - Info mempelai
- `story.html` - Love story timeline
- `event.html` - Detail acara
- `gallery.html` - Galeri foto
- `rsvp.html` - Form RSVP
- `gift.html` - Info hadiah/amplop digital
- `footer.html` - Footer

**Important:**
- Use **Tailwind CSS classes** for styling
- Use **Font Awesome icons** (loaded globally)
- Use **Blade syntax** for dynamic data
- NO custom CSS/JS files needed

**Example `sections/hero.html`:**
```html
<section class="min-h-screen bg-gradient-to-br from-pink-100 to-purple-100 flex items-center justify-center">
  <div class="text-center px-4">
    <h1 class="text-5xl font-bold text-gray-900 mb-4">
      {{ $bride_name ?? 'Bride' }} & {{ $groom_name ?? 'Groom' }}
    </h1>
    <p class="text-xl text-gray-600">
      {{ \Carbon\Carbon::parse($event_date ?? now())->format('d F Y') }}
    </p>
  </div>
</section>
```

### `ornaments/` Directory ⚠️ Optional

Folder untuk elemen dekoratif yang bisa digunakan di berbagai section.

**Example `ornaments/flower-divider.html`:**
```html
<div class="flex justify-center my-8">
  <i class="fas fa-flower text-4xl text-pink-500"></i>
</div>
```
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
  "styling": {
    "colors": {
      "primary": "#dc2626",
      "secondary": "#fbbf24",
      "accent": "#8b5cf6",
      "background": "#ffffff",
      "text": "#1f2937"
    },
    "fonts": {
      "heading": "Playfair Display",
      "body": "Inter",
      "accent": "Dancing Script"
    },
    "spacing": {
      "section": "80px",
      "container": "1200px"
    },
    "borderRadius": {
      "card": "16px",
      "button": "9999px"
    },
    "shadows": {
      "card": "0 10px 30px rgba(0,0,0,0.1)",
      "button": "0 4px 14px rgba(0,0,0,0.15)"
    },
    "custom": {
      "heroHeight": "100vh",
      "overlayOpacity": "0.5"
    }
  },
  "features": {
    "countdown": true,
    "music": true,
    "opening": true,
    "gallery": true,
    "animations": true
  },
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
| `styling` | object | ⚠️ Recommended | CSS variables configuration |
| `features` | object | ⚠️ Recommended | Feature toggles |
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

## Styling System

Template MyAkad menggunakan **Global CSS** yang dikontrol via `template.json`. Tidak perlu membuat file CSS custom!

### How It Works

1. Define styling di `template.json`
2. System inject CSS variables ke page
3. Use CSS variables di HTML dengan `var(--variable-name)`
4. Or use Tailwind CSS classes directly

### Styling Configuration

```json
"styling": {
  "colors": {
    "primary": "#dc2626",      // Main brand color
    "secondary": "#fbbf24",    // Secondary color
    "accent": "#8b5cf6",       // Accent color
    "background": "#ffffff",   // Background color
    "text": "#1f2937"          // Text color
  },
  "fonts": {
    "heading": "Playfair Display",  // Font for headings
    "body": "Inter",                // Font for body text
    "accent": "Dancing Script"      // Font for decorative text
  },
  "spacing": {
    "section": "80px",         // Space between sections
    "container": "1200px"      // Max container width
  },
  "borderRadius": {
    "card": "16px",            // Border radius for cards
    "button": "9999px"         // Border radius for buttons
  },
  "shadows": {
    "card": "0 10px 30px rgba(0,0,0,0.1)",
    "button": "0 4px 14px rgba(0,0,0,0.15)"
  },
  "custom": {
    "heroHeight": "100vh",
    "overlayOpacity": "0.5",
    "anyCustomVariable": "value"
  }
}
```

### Using CSS Variables in HTML

```html
<!-- Using CSS variables -->
<div style="background-color: var(--color-primary);">
  <h1 style="font-family: var(--font-heading); color: var(--color-text);">
    {{ $bride_name ?? '' }} & {{ $groom_name ?? '' }}
  </h1>
</div>

<!-- Or use Tailwind classes (recommended) -->
<div class="bg-gradient-to-br from-pink-100 to-purple-100">
  <h1 class="font-serif text-5xl text-gray-900">
    {{ $bride_name ?? '' }} & {{ $groom_name ?? '' }}
  </h1>
</div>
```

### Available CSS Variables

Based on your `styling` config, these variables are auto-generated:

- `--color-primary`
- `--color-secondary`
- `--color-accent`
- `--color-background`
- `--color-text`
- `--font-heading`
- `--font-body`
- `--font-accent`
- `--spacing-section`
- `--spacing-container`
- `--border-radius-card`
- `--border-radius-button`
- `--shadow-card`
- `--shadow-button`
- Any custom variables you define

### Google Fonts

Fonts specified in `styling.fonts` are automatically loaded from Google Fonts. No need to add `<link>` tags!

**Supported fonts**: Any font available on Google Fonts.

---

## Features Configuration

Enable/disable built-in features via `template.json`:

```json
"features": {
  "countdown": true,      // Countdown timer to event
  "music": true,          // Background music player
  "opening": true,        // Opening screen/splash
  "gallery": true,        // Photo gallery with lightbox
  "animations": true      // Scroll animations
}
```

### Available Features

#### 1. Countdown Timer

**Enable**: `"countdown": true`

**Usage in HTML**:
```html
<div id="countdown" data-date="{{ $event_date ?? '' }}">
  <div class="countdown-item">
    <span class="days">0</span>
    <span class="label">Hari</span>
  </div>
  <div class="countdown-item">
    <span class="hours">0</span>
    <span class="label">Jam</span>
  </div>
  <div class="countdown-item">
    <span class="minutes">0</span>
    <span class="label">Menit</span>
  </div>
  <div class="countdown-item">
    <span class="seconds">0</span>
    <span class="label">Detik</span>
  </div>
</div>
```

**JavaScript API**:
```javascript
// Auto-initialized if feature enabled
// Or manually:
MyAkad.countdown('#countdown', {
  date: '2026-12-25',
  onComplete: function() {
    console.log('Event started!');
  }
});
```

#### 2. Music Player

**Enable**: `"music": true`

**Usage in HTML**:
```html
<button id="music-toggle" class="music-btn">
  <i class="fas fa-music"></i>
</button>
```

**JavaScript API**:
```javascript
// Auto-initialized if feature enabled
// Or manually:
MyAkad.musicPlayer({
  url: '{{ $music_url ?? "" }}',
  autoplay: true,
  loop: true
});
```

#### 3. Opening Screen

**Enable**: `"opening": true`

**Usage in HTML**:
```html
<div id="opening-screen" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
  <div class="text-center">
    <h1 class="text-4xl font-bold mb-4">{{ $bride_name ?? '' }} & {{ $groom_name ?? '' }}</h1>
    <button id="open-invitation" class="btn-primary">
      Buka Undangan
    </button>
  </div>
</div>
```

**JavaScript API**:
```javascript
// Auto-initialized if feature enabled
MyAkad.openingScreen('#opening-screen', {
  onOpen: function() {
    console.log('Invitation opened!');
  }
});
```

#### 4. Gallery Lightbox

**Enable**: `"gallery": true`

**Usage in HTML**:
```html
<div class="gallery-grid">
  @foreach($gallery ?? [] as $item)
    <img src="{{ $item['url'] ?? '' }}" 
         alt="{{ $item['caption'] ?? '' }}"
         class="gallery-item cursor-pointer"
         data-caption="{{ $item['caption'] ?? '' }}">
  @endforeach
</div>
```

**JavaScript API**:
```javascript
// Auto-initialized if feature enabled
MyAkad.gallery('.gallery-item');
```

#### 5. Scroll Animations

**Enable**: `"animations": true`

**Usage in HTML**:
```html
<div class="animate-on-scroll" data-animation="fadeIn">
  Content will fade in on scroll
</div>

<div class="animate-on-scroll" data-animation="slideUp">
  Content will slide up on scroll
</div>
```

**Available animations**: `fadeIn`, `fadeInUp`, `fadeInDown`, `slideUp`, `slideDown`, `zoomIn`

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
└── sections/
```

**Struktur ZIP yang SALAH**:
```
my-template.zip
└── my-template/
    ├── template.json
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
   - Error: "📄 template.json not found in ZIP root."

2. **template.json must be valid JSON**
   - Error: "⚠️ template.json contains invalid JSON syntax"

3. **Required fields in template.json**
   - `name` and `slug` must be present
   - Error: "📋 template.json is missing required fields: name, slug"

4. **Slug format validation**
   - Must be lowercase, alphanumeric, and hyphens only
   - Error: "🔤 Invalid slug format. Slug must contain only lowercase letters, numbers, and hyphens"

5. **Sections array must exist**
   - Must have at least 1 section
   - Error: "📁 template.json must contain a 'sections' array with at least one section"

6. **All section files must exist**
   - Error: "📁 Missing section files: sections/hero.html, sections/story.html"

7. **All ornament files must exist** (if defined)
   - Error: "🎨 Missing ornament files: ornaments/flower.html"

8. **Styling configuration validation** (if defined)
   - Must be object/array
   - Error: "🎨 'styling' must be an object/array in template.json"
   - Warning: "⚠️ Unknown styling key 'unknownKey'. Valid keys: colors, fonts, spacing, borderRadius, shadows, custom"

9. **Features configuration validation** (if defined)
   - Must be object/array
   - Error: "⚙️ 'features' must be an object/array in template.json"
   - Warning: "⚠️ Unknown feature key 'unknownKey'. Valid keys: countdown, music, opening, gallery, animations"

### ✅ Security Validation

1. **No path traversal**
   - Tidak boleh ada: `../`, `../../`, `/etc/`, `C:\`
   - Error: "🔒 ZIP contains unsafe paths (path traversal detected)."

2. **No absolute paths**
   - Tidak boleh ada: `/var/`, `/home/`, `C:\Windows\`
   - Error: "🔒 ZIP contains unsafe paths (path traversal detected)."

3. **Text files only in sections/ornaments**
   - Binary files tidak diperbolehkan
   - Error: "📄 Section file 'sections/hero.html' is not a valid text/HTML file or contains binary data"

4. **No __MACOSX folder**
   - Mac users: clean ZIP before upload
   - Error: "🍎 ZIP contains __MACOSX folder. Please remove this before uploading"

### ✅ Content Validation

1. **Valid HTML in sections**
   - Harus valid HTML/Blade syntax
   - Tidak boleh ada null bytes atau binary content

2. **Valid UTF-8 encoding**
   - All text files must be UTF-8 encoded

### ❌ NO LONGER REQUIRED

1. **~~assets/style.css~~** - Not required anymore! Use `styling` in template.json
2. **~~assets/script.js~~** - Not required anymore! Use `features` in template.json
3. **~~Custom CSS/JS files~~** - System uses global CSS/JS now

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
❌ Template validation failed:
📄 template.json not found in ZIP root.
📁 Missing section files: sections/hero.html
```

### 3. Sync Templates (Alternative)

Jika upload gagal, bisa extract manual ke `storage/app/public/templates/` dan sync:

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

### 1. Use Tailwind CSS

Template system sudah include Tailwind CSS. Gunakan utility classes:

```html
<div class="container mx-auto px-4">
  <h1 class="text-4xl font-bold text-center mb-8">
    {{ $bride_name ?? '' }} & {{ $groom_name ?? '' }}
  </h1>
</div>
```

### 2. Mobile-First Design

Gunakan responsive classes:

```html
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <!-- Content -->
</div>
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
