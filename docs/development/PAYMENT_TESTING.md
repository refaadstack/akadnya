# Payment Testing Guide

## Midtrans Sandbox Configuration

Aplikasi sudah dikonfigurasi dengan Midtrans Sandbox credentials di `.env`:
- Server Key: simpan di `MIDTRANS_SERVER_KEY`
- Client Key: simpan di `MIDTRANS_CLIENT_KEY`
- Environment: Sandbox (Testing)

## Testing Payment Flow

### 1. Persiapan
```bash
# Pastikan migration sudah dijalankan
php artisan migrate

# Pastikan seeder sudah dijalankan
php artisan db:seed

# Jalankan dev server
composer run dev
```

### 2. Login ke Aplikasi
- URL: http://localhost:8000/login
- Email: `user@myakad.test`
- Password: `password`

### 3. Preview Template
1. Buka halaman templates: http://localhost:8000/templates
2. Pilih template (Romantic atau Elegant)
3. Klik "Preview"
4. Isi data undangan di form sidebar
5. Klik "Beli Sekarang"

### 4. Checkout
1. Pilih add-on products yang diinginkan
2. Review total harga
3. Klik "Bayar Sekarang"
4. Popup Midtrans Snap akan muncul

### 5. Test Payment di Sandbox

#### Credit Card Testing
- Card Number: `4811 1111 1111 1114`
- Expiry: Any future date (e.g., `12/25`)
- CVV: `123`
- OTP: `112233`

#### Other Payment Methods
Midtrans Sandbox menyediakan berbagai metode pembayaran untuk testing:
- **GoPay**: Akan auto-approve
- **BCA Virtual Account**: 
  - VA Number akan di-generate
  - Gunakan simulator di Midtrans Dashboard untuk approve
- **Mandiri Bill**: Similar dengan BCA VA
- **Permata VA**: Similar dengan BCA VA

### 6. Webhook Testing

Webhook endpoint: `http://localhost:8000/webhook/midtrans`

Untuk testing lokal, gunakan ngrok atau expose.dev:
```bash
# Install ngrok
ngrok http 8000

# Atau gunakan expose (Laravel)
php artisan serve --host=0.0.0.0 --port=8000
expose share http://localhost:8000
```

Kemudian update webhook URL di Midtrans Dashboard:
- Login ke: https://dashboard.sandbox.midtrans.com/
- Settings > Configuration > Notification URL
- Set ke: `https://your-ngrok-url.ngrok.io/webhook/midtrans`

### 7. Verify Payment

Setelah payment berhasil:
1. Check database `payments` table - status harus `paid`
2. Check database `orders` table - status harus `paid`
3. Check `paid_at` timestamp sudah terisi

## Database Schema

### Orders Table
- `order_number`: Format ORD-YYYYMMDD-XXXXXX
- `status`: pending, paid, failed, refunded, expired
- `total_amount`: Total harga (template + base package + addons)
- `metadata`: JSON berisi template_slug dan preview_data

### Payments Table
- `provider`: midtrans
- `provider_transaction_id`: Order number
- `snap_token`: Token dari Midtrans Snap
- `status`: pending, paid, failed, refunded, expired
- `amount`: Jumlah pembayaran
- `paid_at`: Timestamp pembayaran

## Troubleshooting

### Snap Popup Tidak Muncul
- Check browser console untuk error
- Pastikan Midtrans Snap script sudah loaded di `app.blade.php`
- Verify `snap_token` ada di response

### Webhook Tidak Terima Notifikasi
- Pastikan ngrok/expose running
- Check Midtrans Dashboard > Transactions > Detail > Notification History
- Verify webhook URL sudah benar

### Payment Status Tidak Update
- Check `storage/logs/laravel.log` untuk error
- Verify signature validation di `PaymentService`
- Check database `payments` dan `orders` table

## Next Steps

Setelah payment flow berfungsi:
1. **Task 9**: Implement feature activation setelah payment berhasil
2. **Task 10**: Create invitation management dashboard
3. **Task 11**: Implement invitation editor

## Midtrans Documentation

- Sandbox Dashboard: https://dashboard.sandbox.midtrans.com/
- API Docs: https://docs.midtrans.com/
- Testing Payment: https://docs.midtrans.com/en/technical-reference/sandbox-test
