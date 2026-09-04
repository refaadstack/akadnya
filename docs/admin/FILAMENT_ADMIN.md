# Filament Admin Panel - Akadnya

## Overview

Admin panel untuk Akadnya menggunakan **Filament v5.6** - framework admin panel modern untuk Laravel.

## Akses Admin Panel

- **URL**: `http://localhost:8000/admin` (atau domain Anda + `/admin`)
- **Login**: Hanya user dengan `role = 'admin'` yang bisa akses
- **Credentials Default**:
  - Email: `admin@akadnya.com`
  - Password: (yang Anda set saat `php artisan make:filament-user`)

## Resources yang Tersedia

### Dashboard Widgets

Dashboard admin dilengkapi dengan 5 widgets interaktif:

#### 1. **Stats Overview** (4 Cards)
Menampilkan statistik utama dengan trend:
- **Total Revenue**: Total pendapatan dari orders yang paid
  - Menampilkan persentase increase/decrease vs bulan lalu
  - Mini chart untuk visualisasi trend
  - Format: Rupiah
  
- **Total Users**: Jumlah user (role = 'user')
  - Menampilkan jumlah user baru bulan ini
  - Icon: User Group
  
- **Total Orders**: Jumlah semua orders
  - Menampilkan conversion rate (paid orders / total orders)
  - Icon: Shopping Cart
  
- **Active Invitations**: Jumlah invitation yang published
  - Menampilkan persentase published vs total
  - Icon: Document

#### 2. **Revenue Overview Chart** (Bar Chart)
Chart revenue dan orders dengan filter:
- **Filters**: Last 7 days, Last 30 days, Last 90 days, This year
- **Data**: 
  - Revenue (Rp) - Bar biru
  - Orders count - Bar hijau
- **Interactive**: Hover untuk detail per hari
- **Full Width**: Menggunakan full width dashboard

#### 3. **User Growth Chart** (Line Chart)
Chart pertumbuhan user baru dengan filter:
- **Filters**: Last 7 days, Last 30 days, Last 90 days, This year
- **Data**: New users per day
- **Style**: Line chart dengan fill area (ungu)
- **Smooth**: Tension 0.4 untuk smooth curve
- **Full Width**: Menggunakan full width dashboard

#### 4. **Template Popularity Chart** (Doughnut Chart)
Chart popularitas template berdasarkan usage:
- **Data**: Jumlah invitation per template
- **Type**: Doughnut chart dengan warna berbeda per template
- **Legend**: Bottom position
- **Sorted**: Descending by usage count
- **Full Width**: Menggunakan full width dashboard

#### 5. **Latest Orders Table**
Table 10 orders terbaru:
- **Columns**: Order #, Customer, Status, Total, Date
- **Features**:
  - Order number copyable
  - Status badge dengan warna
  - Sortable columns
  - Searchable
- **Full Width**: Menggunakan full width dashboard

### 1. Products (Produk)
**Path**: `/admin/products`

Mengelola produk base package dan add-ons:
- ✅ Create, Edit, Delete products
- ✅ Toggle active/inactive status
- ✅ Set harga dalam Rupiah
- ✅ Manage recurring products (monthly/yearly)
- ✅ Badge untuk tipe product (Base Package / Add-on)

**Fields**:
- Type: base_package atau addon
- Slug: unique identifier
- Name: nama produk
- Description: deskripsi produk
- Price: harga dalam Rupiah
- Is Active: status aktif/nonaktif
- Is Recurring: apakah berlangganan
- Recurring Interval: monthly/yearly (jika recurring)

### 2. Templates
**Path**: `/admin/templates`

Mengelola template undangan:
- ✅ View all synced templates
- ✅ Edit template details (name, price, active status)
- ✅ **Sync Templates** button - jalankan `templates:sync` command
- ✅ View template usage count (berapa invitation menggunakan template ini)
- ✅ Thumbnail preview
- ✅ Cannot delete templates yang sedang digunakan

**Fields**:
- Slug: auto-generated dari folder name (read-only)
- Name: nama template
- Version: versi template
- Thumbnail URL: URL gambar thumbnail
- Price: harga template (Rp)
- Is Free: apakah template gratis
- Is Active: hanya template aktif yang visible ke user
- Last Synced: timestamp terakhir sync

**Important**: Template tidak dibuat manual di admin panel. Template dibuat dengan:
1. Buat folder di `storage/templates/nama-template/`
2. Buat `template.json` dengan struktur yang benar
3. Klik tombol **Sync Templates** di admin panel
4. Template akan muncul di list

### 3. Users
**Path**: `/admin/users`

Mengelola user/customer:
- ✅ View all users
- ✅ Create new user (manual)
- ✅ Edit user details
- ✅ View user statistics (invitations count, orders count)
- ✅ Badge untuk role (Admin / User)
- ✅ Email verification status
- ✅ 2FA status
- ✅ Cannot delete users dengan active orders

**Fields**:
- Name: nama user
- Email: email address (unique)
- Password: password (hashed, required saat create)
- Role: admin atau user
- Email Verified At: timestamp verifikasi email
- 2FA Confirmed At: timestamp konfirmasi 2FA (read-only)

**Columns**:
- Name, Email, Role
- Verified (✓/✗)
- 2FA (✓/✗)
- Invitations count
- Orders count
- Registered date

### 4. Orders
**Path**: `/admin/orders`

View-only untuk melihat semua order:
- ✅ View all orders (read-only)
- ✅ Filter by status
- ✅ View order details
- ❌ Cannot create/edit orders (orders dibuat otomatis dari checkout)
- ❌ Cannot delete orders

**Columns**:
- Order Number (copyable)
- Customer name & email
- Status badge (pending/paid/failed/expired)
- Total amount (Rupiah)
- Items count
- Paid at timestamp
- Created timestamp

**Status Colors**:
- Pending: Warning (kuning)
- Paid: Success (hijau)
- Failed: Danger (merah)
- Expired: Gray (abu-abu)

### 5. Audit Logs
**Path**: `/admin/audit-logs`

View-only untuk tracking aktivitas sistem:
- ✅ View all audit logs (read-only)
- ✅ Filter by action, user, date
- ❌ Cannot create/edit logs (logs dibuat otomatis)
- ❌ Cannot delete logs (untuk compliance)

**Columns**:
- Time (timestamp lengkap)
- User (nama user atau "System")
- Action badge (created/updated/deleted/published)
- Subject (model type)
- Subject ID
- IP Address

**Action Colors**:
- Created: Success (hijau)
- Updated: Info (biru)
- Deleted: Danger (merah)
- Published: Warning (kuning)

## Features

### Global Features
- ✅ **Search**: Semua table support search
- ✅ **Sort**: Click column header untuk sort
- ✅ **Filters**: Filter data by status, type, dll
- ✅ **Bulk Actions**: Select multiple records untuk bulk delete
- ✅ **Pagination**: Auto pagination untuk large datasets
- ✅ **Responsive**: Mobile-friendly admin panel
- ✅ **Dark Mode**: Support dark mode (toggle di user menu)

### Security
- ✅ **Role-based Access**: Hanya admin yang bisa akses
- ✅ **CSRF Protection**: Built-in CSRF protection
- ✅ **Session Management**: Secure session handling
- ✅ **Password Hashing**: Auto hash password saat create/update user

## Customization

### Dashboard Widgets

Widgets sudah auto-discovered dari `app/Filament/Widgets/`. Urutan widget ditentukan oleh property `$sort`:

```php
protected static ?int $sort = 1; // Semakin kecil, semakin atas
```

**Widget Order**:
1. DashboardStatsOverview (sort: default/1)
2. RevenueChart (sort: 2)
3. UsersChart (sort: 3)
4. TemplatePopularityChart (sort: 4)
5. LatestOrders (sort: 5)

**Customize Widget Width**:
```php
protected int|string|array $columnSpan = 'full'; // full width
protected int|string|array $columnSpan = 2; // 2 columns
```

**Refresh Widget Data**:
Widgets auto-refresh saat filter berubah. Untuk manual refresh:
```php
protected static ?string $pollingInterval = '10s'; // Auto refresh every 10s
```

### Branding
Edit `app/Providers/Filament/AdminPanelProvider.php`:
```php
->brandName('Akadnya.com Admin')
->brandLogo(asset('images/logo.png'))
->favicon(asset('images/favicon.png'))
```

### Colors
```php
->colors([
    'primary' => Color::Amber,
])
```

### Navigation
Urutan menu ditentukan oleh `navigationSort` di masing-masing Resource:
- Products: 1
- Templates: 2
- Users: 3
- Orders: 4
- Audit Logs: 5

## Setup & Installation

### Initial Setup (Already Done)
```bash
# 1. Install Filament
composer require filament/filament

# 2. Install admin panel
php artisan filament:install --panels

# 3. Create notifications table (for database notifications)
php artisan notifications:table
php artisan migrate

# 4. Create admin user
php artisan make:filament-user
```

## Commands

### Create Admin User
```bash
php artisan make:filament-user
```

### Sync Templates
```bash
php artisan templates:sync
```
Atau klik tombol "Sync Templates" di admin panel.

### Clear Cache
```bash
php artisan filament:clear-cached-components
```

## Development

### Create New Resource
```bash
php artisan make:filament-resource ModelName --generate
```

### Create Custom Page
```bash
php artisan make:filament-page PageName
```

### Create Widget
```bash
php artisan make:filament-widget WidgetName
```

### Create Widget
```bash
php artisan make:filament-widget WidgetName
```

**Widget Types**:
- `--stats-overview`: Stats cards widget
- `--chart`: Chart widget (bar, line, pie, doughnut, dll)
- `--table`: Table widget

**Example**:
```bash
# Stats widget
php artisan make:filament-widget SalesStats --stats-overview

# Chart widget
php artisan make:filament-widget SalesChart --chart

# Table widget
php artisan make:filament-widget RecentUsers --table
```

## Troubleshooting

### Cannot Access Admin Panel
1. Pastikan user memiliki `role = 'admin'`
2. Check `User::canAccessPanel()` method di `app/Models/User.php`
3. Clear cache: `php artisan cache:clear`

### Resources Not Showing
1. Check namespace di Resource file
2. Run: `php artisan filament:clear-cached-components`
3. Check `discoverResources()` di AdminPanelProvider

### Sync Templates Button Not Working
1. Check `templates:sync` command exists
2. Check storage/templates directory permissions
3. Check logs: `php artisan pail`

## Next Steps

### Recommended Enhancements
1. **Dashboard Widgets**: Tambah statistics widgets (total users, revenue, dll)
2. **Export**: Tambah export to Excel/CSV untuk reports
3. **Notifications**: Setup database notifications untuk admin
4. **Activity Log**: Integrate dengan spatie/laravel-activitylog
5. **Backup**: Setup automated database backups

### Optional Features
- Multi-language support
- Advanced filters
- Custom actions (e.g., "Send Email to User")
- Bulk operations (e.g., "Activate All Templates")
- Charts & Analytics

## Documentation

- **Filament Docs**: https://filamentphp.com/docs
- **Filament v5 Upgrade**: https://filamentphp.com/docs/5.x/upgrade-guide
- **Community**: https://filamentphp.com/community

## Support

Jika ada masalah dengan admin panel:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console untuk JS errors
3. Run `php artisan pail` untuk live logs
4. Check Filament docs untuk troubleshooting
