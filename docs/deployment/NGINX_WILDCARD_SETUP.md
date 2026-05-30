# Setup Nginx Wildcard Subdomain di Laragon

## Step 1: Buat File Konfigurasi Nginx

1. Buka folder: `C:\laragon\etc\nginx\sites-enabled\`
2. Buat file baru: `myakad.test.conf`
3. Copy isi dari file `nginx-wildcard-config.conf` (ada di root project ini)
4. Paste ke file `myakad.test.conf`
5. Save file

**ATAU** copy file langsung:
```bash
copy C:\laragon\www\myakad\nginx-wildcard-config.conf C:\laragon\etc\nginx\sites-enabled\myakad.test.conf
```

## Step 2: Edit Windows Hosts File

1. Buka Notepad sebagai Administrator
2. Buka file: `C:\Windows\System32\drivers\etc\hosts`
3. Tambahkan baris ini:

```
127.0.0.1      yeli-redho.myakad.test     #laragon magic!
```

**Catatan**: Setiap subdomain baru perlu ditambahkan manual ke hosts file.

## Step 3: Test Konfigurasi Nginx

Buka Command Prompt dan jalankan:

```bash
C:\laragon\bin\nginx\nginx-1.x.x\nginx.exe -t
```

Harusnya muncul:
```
nginx: the configuration file C:\laragon\bin\nginx\nginx-1.x.x/conf/nginx.conf syntax is ok
nginx: configuration file C:\laragon\bin\nginx\nginx-1.x.x/conf/nginx.conf test is successful
```

## Step 4: Restart Nginx di Laragon

1. Buka Laragon
2. Klik **Stop All**
3. Klik **Start All**

## Step 5: Test Subdomain

Buka browser dan akses:
```
http://yeli-redho.myakad.test
```

Atau test dengan ping:
```bash
ping yeli-redho.myakad.test
```

## Troubleshooting

### Error: "nginx: [emerg] could not build server_names_hash"

Tambahkan di file `C:\laragon\etc\nginx\nginx.conf` di dalam block `http`:

```nginx
http {
    server_names_hash_bucket_size 64;
    # ... konfigurasi lainnya
}
```

### Error: "404 Not Found"

Pastikan:
1. Path `root` di config benar: `C:/laragon/www/myakad/public`
2. File `index.php` ada di folder `public`
3. Nginx sudah di-restart

### Error: "502 Bad Gateway"

Pastikan PHP-FPM berjalan:
1. Buka Laragon
2. Pastikan PHP sudah running
3. Check port PHP-FPM (default: 9000)

### Subdomain Tidak Resolve

Pastikan:
1. Subdomain sudah ditambahkan ke hosts file
2. Format: `127.0.0.1      subdomain.myakad.test`
3. Tidak ada typo
4. Flush DNS: `ipconfig /flushdns`

## Solusi Alternatif: Acrylic DNS Proxy

Untuk menghindari edit hosts file setiap kali ada subdomain baru:

1. Download Acrylic DNS Proxy: https://mayakron.altervista.org/support/acrylic/Home.htm
2. Install Acrylic
3. Edit `AcrylicHosts.txt`:
   ```
   127.0.0.1 *.myakad.test
   ```
4. Restart Acrylic service
5. Set DNS komputer ke `127.0.0.1`

Dengan Acrylic, **semua subdomain** otomatis resolve tanpa edit hosts file!

## Production Setup

Di production, Anda hanya perlu:

1. **Setup DNS Wildcard** di domain registrar:
   ```
   A     @     -> IP_SERVER
   A     *     -> IP_SERVER
   ```

2. **Nginx Config** di server (sama seperti di atas, tapi ganti domain):
   ```nginx
   server_name myakad.id *.myakad.id;
   ```

3. **SSL Certificate** dengan wildcard:
   ```bash
   certbot certonly --nginx -d myakad.id -d *.myakad.id
   ```

Selesai! Semua subdomain otomatis jalan tanpa setup tambahan.
