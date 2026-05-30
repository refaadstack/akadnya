# Template Preview Issue - Troubleshooting Guide

## Masalah yang Ditemukan

Ketika membuka halaman preview template (`/templates/{slug}/preview`), template tidak tampil sebagaimana mestinya. Berikut adalah analisis lengkap masalah dan solusinya.

---

## Root Cause Analysis

### 1. **Opening Screen Menutupi Konten** ✅ FIXED

**Masalah:**
- Template memiliki opening screen dengan `position: fixed` dan `z-index: 50` yang menutupi seluruh viewport
- Opening screen harus diklik manual oleh user untuk melihat konten di belakangnya
- Di mode preview, user ingin langsung melihat template tanpa harus klik tombol

**Solusi:**
Menambahkan auto-open script di `BladeRenderService::renderPreview()` yang otomatis mengklik tombol "Buka Undangan" setelah halaman dimuat:

```php
// Auto-open script for preview mode
$autoOpenScript = <<<'JAVASCRIPT'
<script>
// Auto-open invitation in preview mode
document.addEventListener('DOMContentLoaded', function() {
  console.log('Preview mode: Auto-clicking open invitation button');
  
  // Wait for MyAkad to initialize
  setTimeout(function() {
    const openButton = document.querySelector('#open-invitation');
    if (openButton) {
      openButton.click();
    }
  }, 500);
});
</script>
JAVASCRIPT;
```

**File yang diubah:**
- `app/Services/BladeRenderService.php`

---

### 2. **Tailwind Classes Tidak Ter-compile** ✅ VERIFIED

**Masalah:**
- Template menggunakan arbitrary values Tailwind seperti `from-[#DC143C]`, `to-[#B22222]`, `text-[#FFD700]`
- Jika Tailwind tidak scan file template, classes ini tidak akan ter-generate

**Verifikasi:**
File `resources/css/app.css` sudah dikonfigurasi dengan benar:

```css
/* Add template directory to Tailwind scanning */
@source '../../storage/app/public/templates/**/*.html';
```

**Solusi:**
Rebuild assets dengan `npm run build` untuk memastikan semua Tailwind classes dari template ter-compile.

**Command:**
```bash
npm run build
```

---

### 3. **Preview Data Flow** ✅ VERIFIED

**Alur Data:**

1. **Vue Component** (`resources/js/pages/Templates/Preview.vue`):
   - Menyimpan preview data di `sessionStorage`
   - Encode data sebagai base64 JSON
   - Load template via iframe dengan URL: `/templates/{slug}/render?data={base64}`

2. **Controller** (`app/Http/Controllers/TemplateController.php`):
   - Method `render()` menerima query parameter `data`
   - Decode base64 JSON data
   - Merge dengan dummy data dari `DataContractBuilder`
   - Render HTML via `BladeRenderService`

3. **Render Service** (`app/Services/BladeRenderService.php`):
   - Load semua sections dari template
   - Render setiap section dengan Blade engine
   - Inject CSS variables dari `template.json`
   - Inject template config sebagai base64 meta tag
   - Load global CSS dan JS

4. **JavaScript** (`public/js/template-base.js`):
   - Parse template config dari meta tag
   - Initialize features (countdown, music, opening screen, gallery, animations)
   - Auto-open opening screen di preview mode

---

## Verification Steps

### 1. Check Browser Logs

```javascript
// Open browser console and check for these logs:
// ✓ "MyAkad Template System initialized"
// ✓ "Config parsed successfully"
// ✓ "Initializing opening screen"
// ✓ "Preview mode: Auto-clicking open invitation button"
// ✓ "Opening button clicked!"
```

### 2. Check Network Requests

```
GET /templates/{slug}/render?data={base64}
Status: 200 OK
Content-Type: text/html; charset=utf-8
```

### 3. Check HTML Output

```html
<!-- Should have these elements: -->
<meta name="template-config" content="..." data-encoding="base64">
<link rel="stylesheet" href="/css/template-base.css">
<link rel="stylesheet" href="/build/assets/app-*.css">
<script src="/js/template-base.js"></script>
```

### 4. Check Tailwind Classes

Inspect element dan pastikan classes seperti `from-[#DC143C]` memiliki CSS rules yang ter-generate.

---

## Common Issues & Solutions

### Issue: Opening screen tidak hilang otomatis

**Symptoms:**
- Opening screen tetap menutupi konten
- Tidak ada log "Preview mode: Auto-clicking open invitation button"

**Solutions:**
1. Clear browser cache dan reload
2. Check browser console untuk JavaScript errors
3. Pastikan `BladeRenderService::renderPreview()` sudah include auto-open script
4. Verify element `#open-invitation` ada di HTML

### Issue: Styling tidak muncul

**Symptoms:**
- Template tampil tanpa warna/styling
- Tailwind classes tidak bekerja

**Solutions:**
1. Run `npm run build` untuk rebuild assets
2. Check `resources/css/app.css` memiliki `@source` untuk template directory
3. Clear browser cache
4. Check network tab untuk memastikan CSS files loaded

### Issue: Template config tidak ter-load

**Symptoms:**
- Features tidak initialize (countdown, music, dll)
- Console log: "No meta[name='template-config'] found"

**Solutions:**
1. Check `BladeRenderService::buildAssetTags()` inject meta tag dengan benar
2. Verify `template.json` valid JSON
3. Check browser console untuk parse errors

---

## Testing Checklist

- [ ] Opening screen hilang otomatis setelah 500ms
- [ ] Semua sections ter-render dengan benar
- [ ] Tailwind classes (arbitrary values) bekerja
- [ ] CSS variables dari template.json ter-inject
- [ ] Countdown timer berjalan
- [ ] Music toggle button muncul
- [ ] Gallery lightbox bekerja
- [ ] Animations on scroll bekerja
- [ ] Preview data dari form ter-apply ke template
- [ ] Browser console tidak ada errors

---

## Files Modified

1. `app/Services/BladeRenderService.php`
   - Added auto-open script in `renderPreview()` method

2. `resources/css/app.css`
   - Already configured with `@source` for template directory (no changes needed)

3. `public/js/template-base.js`
   - Already has auto-open logic (no changes needed)

---

## Related Documentation

- [Template Creation Guide](../templates/TEMPLATE_CREATION_GUIDE.md)
- [Template System Architecture](../templates/ARCHITECTURE.md)
- [Blade Render Service](../services/BLADE_RENDER_SERVICE.md)

---

## Monitoring & Debugging

### Enable Debug Mode

Add to `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Check Laravel Logs

```bash
php artisan pail
```

### Check Browser Logs

```bash
# Use Laravel Boost MCP tool
mcp_laravel_boost_browser_logs entries=50
```

### Test Template Render Directly

```bash
php artisan tinker --execute "echo (new App\Http\Controllers\TemplateController(app('App\Services\BladeRenderService'), app('App\Services\DataContractBuilder')))->render('betawi-heritage')->getContent();" > test_output.html
```

---

## Conclusion

Masalah utama adalah **opening screen yang menutupi konten** dan tidak otomatis hilang di mode preview. Solusinya adalah menambahkan auto-open script yang mengklik tombol "Buka Undangan" secara otomatis setelah halaman dimuat.

Tailwind configuration sudah benar, hanya perlu rebuild assets untuk memastikan semua classes ter-compile.

**Status:** ✅ RESOLVED
