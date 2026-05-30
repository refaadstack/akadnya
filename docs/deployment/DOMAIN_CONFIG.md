# Konfigurasi Domain

## Perubahan Terbaru

Domain aplikasi sekarang dikonfigurasi melalui environment variable `APP_DOMAIN` di file `.env`, bukan lagi hardcoded di kode.

## Cara Setup

### 1. Tambahkan ke file `.env`

Buka file `.env` Anda dan tambahkan baris berikut:

```env
APP_DOMAIN=myakad.id
```

Untuk development lokal, gunakan:

```env
APP_DOMAIN=myakad.test
```

### 2. Clear Config Cache

Setelah menambahkan `APP_DOMAIN`, jalankan:

```bash
php artisan config:clear
```

## Penggunaan

### Backend (PHP)

Domain dapat diakses menggunakan:

```php
config('app.domain')
```

Contoh penggunaan:

```php
// Generate subdomain URL
$url = 'https://' . $invitation->subdomain . '.' . config('app.domain');

// Check if host matches domain
$appDomain = config('app.domain');
if (str_ends_with($host, '.' . $appDomain)) {
    // Handle subdomain
}
```

### Frontend (Vue)

Domain tersedia di semua komponen Vue melalui `$page.props.appDomain`:

```vue
<template>
  <p>Subdomain gratis (namaanda.{{ $page.props.appDomain }})</p>
</template>
```

## File yang Sudah Diupdate

### Backend
- ✅ `config/app.php` - Menambahkan konfigurasi `domain`
- ✅ `app/Http/Middleware/ResolveInvitation.php` - Menggunakan `config('app.domain')`
- ✅ `app/Http/Controllers/DashboardController.php` - Menggunakan `config('app.domain')`
- ✅ `app/Models/Invitation.php` - Method `getPublicUrl()` menggunakan `config('app.domain')`
- ✅ `app/Http/Middleware/HandleInertiaRequests.php` - Share `appDomain` ke frontend

### Frontend
- ✅ `resources/js/pages/Welcome.vue` - Menggunakan `$page.props.appDomain`
- ✅ `resources/js/pages/Checkout/Index.vue` - Menggunakan `$page.props.appDomain`

## Keuntungan

1. **Fleksibilitas**: Ganti domain tanpa edit kode, cukup update `.env`
2. **Multi-environment**: Development bisa pakai `.test`, production pakai `.id`
3. **Centralized**: Satu tempat konfigurasi untuk seluruh aplikasi
4. **Type-safe**: Frontend mendapat domain dari backend, tidak ada hardcode

## Contoh Konfigurasi

### Development (Laragon)
```env
APP_URL=http://myakad.test
APP_DOMAIN=myakad.test
```

### Production
```env
APP_URL=https://myakad.id
APP_DOMAIN=myakad.id
```

### Staging
```env
APP_URL=https://staging.myakad.id
APP_DOMAIN=staging.myakad.id
```
