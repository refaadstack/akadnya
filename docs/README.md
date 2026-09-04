# Akadnya Documentation

Dokumentasi lengkap untuk project Akadnya.

## Struktur Folder

### `/templates/`
Dokumentasi terkait template system dan template development:

**🎯 Preset Templates (Ready to Use):**
- **BETAWI_HERITAGE_PRESET.md** - ✅ Template Betawi Heritage siap pakai (PRODUCTION READY)
- **STYLING_PATTERNS.md** - 🎨 Pattern reference untuk semua styling
- **preset-visual-reference.html** - 👁️ Visual guide (buka di browser)

**📖 General Documentation:**
- **TEMPLATE_CREATION_GUIDE.md** - 🆕 Panduan lengkap membuat template (ZIP upload)
- **QUICK_REFERENCE.md** - ⚡ Cheat sheet 1 halaman untuk developer
- **MOBILE_ONLY_LAYOUT.md** - 📱 ✅ **ACTIVE** - Panduan layout mobile-only (480px fixed)

### `/admin/`
Dokumentasi terkait admin panel dan Filament:
- **FILAMENT_ADMIN.md** - Dokumentasi Filament admin panel

### `/deployment/`
Dokumentasi terkait deployment dan infrastructure:
- **DOMAIN_CONFIG.md** - Konfigurasi domain dan subdomain

### `/development/`
Dokumentasi terkait development dan testing:
- **PAYMENT_TESTING.md** - Panduan testing payment integration

## Quick Links

### Untuk Template Designers
1. **START HERE**: Baca [BETAWI_HERITAGE_PRESET.md](templates/BETAWI_HERITAGE_PRESET.md) untuk melihat template preset yang sudah jadi ✅
2. Lihat [preset-visual-reference.html](templates/preset-visual-reference.html) di browser untuk visual guide
3. Baca [STYLING_PATTERNS.md](templates/STYLING_PATTERNS.md) untuk pattern reference
4. Baca [TEMPLATE_CREATION_GUIDE.md](templates/TEMPLATE_CREATION_GUIDE.md) untuk membuat template baru dari scratch
5. Gunakan [QUICK_REFERENCE.md](templates/QUICK_REFERENCE.md) sebagai cheat sheet

### Untuk Developers
1. Lihat `.agents/specs/template-system-refactor/` untuk spec lengkap template system
2. Baca [FILAMENT_ADMIN.md](admin/FILAMENT_ADMIN.md) untuk admin panel development

### Untuk Admin
1. Upload template via Filament admin panel
2. Sync templates: `php artisan templates:sync`
3. Preview templates di `/templates/{slug}/preview`

## File Locations

- **Templates**: `storage/templates/{slug}/`
- **Migrations**: `database/migrations/`
- **Models**: `app/Models/`
- **Services**: `app/Services/`
- **Controllers**: `app/Http/Controllers/`
- **Tests**: `tests/Feature/` dan `tests/Unit/`

## Development Commands

```bash
# Sync templates from storage
php artisan templates:sync

# Run tests
php artisan test --compact

# Format code
vendor/bin/pint --format agent

# Build frontend
npm run build

# Dev server
npm run dev
```

## Support

Untuk pertanyaan atau issue, hubungi tim development Akadnya.
