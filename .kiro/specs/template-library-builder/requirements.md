# Template Library Builder - Requirements

## Goal
Generate banyak template undangan pernikahan dengan tema budaya Indonesia yang siap pakai, mengikuti pattern dari Palembang Megah dan dokumentasi di `__templates/`.

## What We Have
- ✅ Template system yang sudah jalan (Palembang Megah)
- ✅ Global CSS system (template-components.css, template-base.css)
- ✅ CSS Variables untuk theming
- ✅ Dokumentasi lengkap di `__templates/`
- ✅ BladeRenderService untuk rendering

## What We Need
**10-15 template siap pakai** dengan tema budaya Indonesia:

### Tema Template
1. **Jawa Klasik** - Coklat, emas, batik
2. **Sunda Modern** - Hijau, putih, minimalis
3. **Minang Elegan** - Merah, emas, rumah gadang
4. **Betawi Heritage** - Merah, kuning, ondel-ondel (sudah ada dokumentasi)
5. **Bali Exotic** - Ungu, emas, pura
6. **Aceh Traditional** - Hijau, emas, masjid
7. **Bugis Royal** - Biru, emas, perahu pinisi
8. **Dayak Ethnic** - Coklat, merah, mandau
9. **Batak Toba** - Merah, hitam, ulos
10. **Madura Colorful** - Merah, kuning, karapan sapi

### Setiap Template Harus Punya
1. **template.json** dengan:
   - Color scheme sesuai tema
   - Font pairing yang cocok
   - Metadata lengkap

2. **8 Section Files**:
   - opening.html (opening screen)
   - hero.html (hero dengan countdown)
   - couple.html (profil mempelai)
   - story.html (kisah cinta)
   - event.html (detail acara)
   - gallery.html (galeri foto)
   - rsvp.html (RSVP & guestbook)
   - gift.html (amplop digital)
   - footer.html (penutup)

3. **Ornament Files** (opsional, sesuai tema):
   - Ornamen budaya (batik, songket, ukiran, dll)
   - Divider/pembatas section

## Implementation Approach

### Phase 1: Template Generator Script
Buat PHP script/command untuk generate template:
```bash
php artisan template:generate {theme} {slug}
```

Script akan:
1. Baca template base dari Palembang Megah
2. Generate color scheme sesuai tema
3. Generate semua section files dengan class global
4. Generate template.json
5. Save ke storage/app/public/templates/{slug}/

### Phase 2: Batch Generate
Generate semua 10 template sekaligus:
```bash
php artisan template:generate-all
```

### Phase 3: Validation & Preview
Test semua template:
```bash
php artisan template:validate-all
```

## Success Criteria
- ✅ 10-15 template siap pakai
- ✅ Semua template pakai Global CSS system
- ✅ Semua template responsive (mobile-first)
- ✅ Semua template punya color scheme unik
- ✅ Preview berfungsi untuk semua template
- ✅ Database sync otomatis

## Technical Details

### Color Schemes (dari dokumentasi)
```php
$themes = [
    'jawa-klasik' => [
        'primary' => '#8B4513',    // Coklat
        'secondary' => '#FFD700',  // Emas
        'accent' => '#654321',     // Coklat tua
    ],
    'sunda-modern' => [
        'primary' => '#2E7D32',    // Hijau
        'secondary' => '#FFFFFF',  // Putih
        'accent' => '#81C784',     // Hijau muda
    ],
    // ... dst
];
```

### Template Structure (dari Palembang Megah)
```
storage/app/public/templates/{slug}/
├── template.json
├── sections/
│   ├── opening.html
│   ├── hero.html
│   ├── couple.html
│   ├── story.html
│   ├── event.html
│   ├── gallery.html
│   ├── rsvp.html
│   ├── gift.html
│   └── footer.html
└── ornaments/ (optional)
    └── divider.html
```

### Global CSS Classes (sudah ada)
- template-opening
- template-btn
- template-card
- template-title
- template-text-white
- template-text-secondary
- dll (lihat template-components.css)

## Next Steps
1. Buat Artisan command `template:generate`
2. Buat template base/skeleton
3. Generate 10 template sekaligus
4. Test preview semua template
5. Sync ke database
