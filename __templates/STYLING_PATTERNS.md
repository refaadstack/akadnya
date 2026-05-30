# Styling Patterns - MyAkad Templates

## 🎨 Visual Reference Guide

Panduan visual untuk semua pattern styling yang digunakan di template MyAkad.

**PENTING**: Semua template menggunakan **Global CSS system** dengan class dari `template-components.css` dan CSS Variables dari `template.json`. **TIDAK menggunakan Tailwind arbitrary values**.

---

## 📦 Card Patterns

### Standard Card
```html
<div class="template-card">
  <h3 class="template-title template-text-primary template-mb-2">Title</h3>
  <p class="template-text">Content</p>
</div>
```

**Digunakan di:**
- ✅ Couple section (profil mempelai)
- ✅ Event section (akad & resepsi)
- ✅ RSVP section (form & guestbook)
- ✅ Gift section (payment methods)
- ✅ Footer section (thank you note)

**Visual:**
- Background: White
- Border: 2px solid var(--color-secondary)
- Border radius: 1rem
- Shadow: 0 4px 12px rgba(0,0,0,0.1)
- Padding: 1.5rem (mobile), 2rem (desktop)

**CSS Variables Used:**
- `--color-secondary` untuk border
- `--color-primary` untuk title color

---

## 🔘 Button Patterns

### Primary Button
```html
<button class="template-btn template-btn-lg">
  <i class="fas fa-icon"></i>
  Button Text
</button>
```

**Digunakan di:**
- ✅ Opening screen (Buka Undangan)
- ✅ Event section (Lihat Lokasi)
- ✅ RSVP section (Kirim Konfirmasi)
- ✅ Gift section (Salin)

**Visual:**
- Background: var(--color-secondary)
- Text: var(--color-primary)
- Border radius: Full (rounded-full)
- Padding: 0.875rem 1.75rem (mobile), 1rem 2rem (desktop)
- Hover: scale 105% (desktop only)
- Icon: Font Awesome with gap 0.5rem

**CSS Classes:**
- `template-btn` - Base button style
- `template-btn-lg` - Larger button
- `template-btn-primary` - Primary color variant

### Secondary Button (Copy)
```html
<button class="template-btn copy-button" data-copy="text">
  <i class="fas fa-copy"></i>
  Salin
</button>
```

---

## 📝 Form Patterns

### Text Input
```html
<input 
  type="text" 
  class="w-full px-4 py-3 border-2 border-[#fbbf24] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#dc2626] focus:border-transparent transition-all"
  placeholder="Placeholder text"
>
```

**Visual:**
- Border: 2px solid yellow (#fbbf24)
- Border radius: lg (0.5rem)
- Padding: px-4 py-3
- Focus: Red ring (#dc2626)

### Select Dropdown
```html
<select class="w-full px-4 py-3 border-2 border-[#fbbf24] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#dc2626] focus:border-transparent transition-all">
  <option value="">Pilih opsi</option>
  <option value="1">Option 1</option>
</select>
```

### Textarea
```html
<textarea 
  rows="4" 
  class="w-full px-4 py-3 border-2 border-[#fbbf24] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#dc2626] focus:border-transparent transition-all resize-none"
  placeholder="Placeholder text"
></textarea>
```

### Label
```html
<label class="block text-gray-800 font-semibold mb-2 uppercase text-sm tracking-wider">
  Label Text <span class="text-[#dc2626]">*</span>
</label>
```

**Digunakan di:**
- ✅ RSVP section (form konfirmasi)

---

## 📋 Section Title Pattern

### Standard Section Title
```html
<div class="text-center mb-16 animate-on-scroll">
  <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4" style="font-family: 'Playfair Display', serif;">
    Section Title
  </h2>
  <div class="w-24 h-1 bg-gradient-to-r from-transparent via-[#fbbf24] to-transparent mx-auto"></div>
  <p class="text-gray-700 mt-6">
    Section description
  </p>
</div>
```

**Digunakan di:**
- ✅ Couple section
- ✅ Story section
- ✅ Event section
- ✅ Gallery section
- ✅ RSVP section
- ✅ Gift section

**Visual:**
- Title: 4xl/5xl, bold, Playfair Display
- Divider: Yellow gradient line (w-24 h-1)
- Description: Gray-700, mt-6

---

## 🎭 Icon Patterns

### Large Icon (Section Header)
```html
<div class="mb-6">
  <i class="fas fa-mosque text-6xl text-[#dc2626]"></i>
</div>
```

**Digunakan di:**
- ✅ Event section (mosque, glass-cheers)
- ✅ Footer section (heart)

### Medium Icon (Card)
```html
<div class="w-20 h-20 bg-gradient-to-br from-[#dc2626] to-[#b91c1c] rounded-full flex items-center justify-center shadow-lg">
  <i class="fas fa-university text-4xl text-[#fbbf24]"></i>
</div>
```

**Digunakan di:**
- ✅ Gift section (payment methods)

### Small Icon (Button)
```html
<i class="fas fa-paper-plane"></i>
```

**Digunakan di:**
- ✅ All buttons

### Avatar Icon (Guestbook)
```html
<div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#dc2626] to-[#b91c1c] flex items-center justify-center text-white font-bold text-xl">
  A
</div>
```

**Digunakan di:**
- ✅ RSVP section (guestbook messages)

---

## 🎨 Background Patterns

### Cream Background with Pattern
```html
<section class="py-20 md:py-32 bg-[#fef3c7] relative overflow-hidden">
  <!-- Background Pattern -->
  <div class="absolute inset-0 opacity-5">
    <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgba(220, 38, 38, 0.3) 1px, transparent 0); background-size: 32px 32px;"></div>
  </div>
  
  <div class="container mx-auto px-6 max-w-4xl relative z-10">
    <!-- Content -->
  </div>
</section>
```

**Digunakan di:**
- ✅ Couple section
- ✅ Event section
- ✅ RSVP section

### Gradient Background
```html
<section class="py-20 md:py-32 bg-gradient-to-b from-white to-[#fef3c7]">
  <!-- Content -->
</section>
```

**Digunakan di:**
- ✅ Story section
- ✅ Gift section

### Red Gradient (Footer)
```html
<footer class="py-16 md:py-20 bg-gradient-to-b from-[#dc2626] to-[#991b1b] text-white">
  <!-- Content -->
</footer>
```

---

## 🎯 Spacing Patterns

### Section Spacing
```html
<section class="py-20 md:py-32">
  <!-- py-20 = 5rem mobile, py-32 = 8rem desktop -->
</section>
```

### Container
```html
<div class="container mx-auto px-6 max-w-4xl">
  <!-- Standard container for most sections -->
</div>

<div class="container mx-auto px-6 max-w-6xl">
  <!-- Wider container for couple/event sections -->
</div>
```

### Content Spacing
```html
<div class="space-y-6">
  <!-- Vertical spacing between elements -->
</div>

<div class="mb-16">
  <!-- Bottom margin for section titles -->
</div>

<div class="mb-12">
  <!-- Bottom margin for content blocks -->
</div>
```

---

## 🎬 Animation Patterns

### Animate on Scroll
```html
<div class="animate-on-scroll">
  <!-- Content will fade in when scrolled into view -->
</div>
```

**Digunakan di:**
- ✅ All section titles
- ✅ All cards
- ✅ All content blocks

### Hover Animations
```html
<!-- Button hover -->
<button class="hover:scale-105 transition-all">

<!-- Card hover (optional) -->
<div class="hover:shadow-2xl transition-shadow">
```

---

## 📱 Responsive Patterns

### Grid Layout
```html
<!-- 1 column mobile, 2 columns desktop -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
  <!-- Items -->
</div>

<!-- 2 columns mobile, 4 columns desktop -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
  <!-- Items -->
</div>
```

### Text Sizing
```html
<!-- Responsive text -->
<h1 class="text-4xl md:text-5xl">
<h2 class="text-3xl md:text-4xl">
<p class="text-base md:text-lg">
```

### Padding/Margin
```html
<!-- Responsive spacing -->
<div class="py-20 md:py-32">
<div class="p-8 md:p-12">
<div class="mb-8 md:mb-12">
```

---

## 🎨 Color Reference

### Primary Colors
```css
/* Red (Primary) */
#dc2626 - Main red
#b91c1c - Darker red (hover)
#991b1b - Darkest red (footer)

/* Yellow (Secondary) */
#fbbf24 - Main yellow
#f59e0b - Accent yellow

/* Cream (Background) */
#fef3c7 - Light cream
#fde68a - Border cream
```

### Text Colors
```css
/* Gray Scale */
#1f2937 - Dark gray (main text)
#6b7280 - Medium gray (secondary text)
#9ca3af - Light gray (placeholder)
```

### Usage
```html
<!-- Text -->
<p class="text-gray-800">Main text</p>
<p class="text-gray-700">Secondary text</p>
<p class="text-gray-600">Tertiary text</p>
<p class="text-gray-500">Muted text</p>

<!-- Background -->
<div class="bg-[#dc2626]">Red background</div>
<div class="bg-[#fbbf24]">Yellow background</div>
<div class="bg-[#fef3c7]">Cream background</div>

<!-- Border -->
<div class="border-2 border-[#fbbf24]">Yellow border</div>
```

---

## 📐 Border Radius Reference

```html
<!-- Rounded corners -->
<div class="rounded-lg">    <!-- 0.5rem -->
<div class="rounded-xl">    <!-- 0.75rem -->
<div class="rounded-2xl">   <!-- 1rem -->
<div class="rounded-3xl">   <!-- 1.5rem -->
<div class="rounded-full">  <!-- 9999px (circle) -->
```

**Usage:**
- Cards: `rounded-2xl`
- Buttons: `rounded-full`
- Inputs: `rounded-lg`
- Images: `rounded-3xl`

---

## 🎯 Font Patterns

### Headings
```html
<h1 style="font-family: 'Playfair Display', serif;">
  Main Heading
</h1>
```

### Body Text
```html
<p class="font-sans">
  Body text (Inter font)
</p>
```

### Font Weights
```html
<p class="font-normal">   <!-- 400 -->
<p class="font-semibold"> <!-- 600 -->
<p class="font-bold">     <!-- 700 -->
```

---

## ✅ Checklist: Styling Consistency

Setiap section HARUS memiliki:
- ✅ Section title dengan divider kuning
- ✅ Card dengan border kuning 2px
- ✅ Button merah dengan rounded-full
- ✅ Font Awesome icons (fas fa-*)
- ✅ Playfair Display untuk heading
- ✅ Tailwind utility classes
- ✅ Responsive design (mobile-first)
- ✅ Animate on scroll
- ✅ Consistent spacing (py-20 md:py-32)

---

**Status:** ✅ ALL PATTERNS DOCUMENTED  
**Version:** 1.0.0  
**Last Updated:** May 11, 2026
