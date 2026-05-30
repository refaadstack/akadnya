# Template Upload Error Handling Guide

## Overview

Sistem upload template di Filament Admin memiliki validasi lengkap untuk memastikan template yang diupload sesuai dengan struktur yang diharapkan. Dokumen ini menjelaskan semua error yang mungkin muncul dan cara mengatasinya.

---

## 🎯 Cara Upload Template

### Via Admin Panel

1. Login ke Admin Panel (`/admin`)
2. Navigasi ke **Templates**
3. Klik tombol **"Upload Template ZIP"**
4. Pilih file ZIP template
5. Klik **"Upload & Process"**

### Via Create Form

1. Login ke Admin Panel
2. Navigasi ke **Templates** → **Create**
3. Upload ZIP di section "Upload Template ZIP"
4. Form akan otomatis terisi dari `template.json`
5. Klik **"Create"**

---

## ❌ Common Errors & Solutions

### 1. **📄 template.json not found in ZIP root**

**Error Message:**
```
template.json not found in ZIP root. This file is required and must be in the root of the ZIP.
```

**Penyebab:**
- File `template.json` tidak ada di root ZIP
- File ada tapi di dalam subfolder

**Solusi:**
```
✅ Correct Structure:
my-template.zip
├── template.json    ← Harus di root
├── sections/
└── assets/

❌ Wrong Structure:
my-template.zip
└── my-template/     ← Jangan ada parent folder
    ├── template.json
    ├── sections/
    └── assets/
```

**Fix:**
1. Extract ZIP
2. Pastikan `template.json` ada di root folder
3. Re-zip tanpa parent folder
4. Upload ulang

---

### 2. **⚠️ template.json contains invalid JSON syntax**

**Error Message:**
```
template.json contains invalid JSON syntax: Syntax error
```

**Penyebab:**
- JSON tidak valid (missing comma, bracket, quotes)
- Encoding file salah

**Solusi:**
1. Validasi JSON di [jsonlint.com](https://jsonlint.com)
2. Pastikan:
   - Semua string pakai double quotes `"`
   - Tidak ada trailing comma
   - Bracket `{}` dan `[]` balance
3. Save dengan encoding UTF-8

**Contoh Valid JSON:**
```json
{
  "name": "Romantic Wedding",
  "slug": "romantic",
  "version": "1.0.0",
  "sections": [
    {"file": "cover.html", "label": "Cover"},
    {"file": "opening.html", "label": "Opening"}
  ]
}
```

---

### 3. **📋 template.json is missing required fields**

**Error Message:**
```
template.json is missing required fields: slug, name. These fields are mandatory.
```

**Penyebab:**
- Field `name` atau `slug` tidak ada
- Field ada tapi kosong/null

**Solusi:**
Pastikan `template.json` punya minimal:
```json
{
  "name": "Template Name",     // ✅ Required
  "slug": "template-slug",     // ✅ Required
  "sections": [...]            // ✅ Required
}
```

**Optional fields:**
```json
{
  "version": "1.0.0",
  "thumbnail": "assets/images/thumb.jpg",
  "is_free": false,
  "price": 50000,
  "ornaments": [...]
}
```

---

### 4. **🔤 Invalid slug format**

**Error Message:**
```
Invalid slug format. Slug must contain only lowercase letters, numbers, and hyphens.
```

**Penyebab:**
- Slug mengandung karakter tidak valid
- Slug pakai uppercase atau spasi

**Solusi:**
```
❌ Wrong:
"slug": "Romantic Wedding"
"slug": "romantic_wedding"
"slug": "romantic.wedding"

✅ Correct:
"slug": "romantic-wedding"
"slug": "romantic"
"slug": "wedding-2024"
```

**Rules:**
- Hanya lowercase `a-z`
- Angka `0-9`
- Hyphen `-`
- Tidak boleh spasi, underscore, atau special characters

---

### 5. **📁 Missing section files**

**Error Message:**
```
Missing section files: sections/cover.html, sections/opening.html
```

**Penyebab:**
- File section yang didefinisikan di `template.json` tidak ada di folder `sections/`
- Nama file tidak match

**Solusi:**
1. Cek `template.json`:
```json
{
  "sections": [
    {"file": "cover.html", "label": "Cover"},
    {"file": "opening.html", "label": "Opening"}
  ]
}
```

2. Pastikan file ada di ZIP:
```
my-template.zip
├── template.json
└── sections/
    ├── cover.html      ← Harus ada
    └── opening.html    ← Harus ada
```

3. Nama file harus **exact match** (case-sensitive)

---

### 6. **🎨 Required file assets/style.css not found**

**Error Message:**
```
Required file assets/style.css not found. This stylesheet is mandatory for all templates.
```

**Penyebab:**
- File `style.css` tidak ada di folder `assets/`
- Nama file salah (typo)

**Solusi:**
```
✅ Correct Structure:
my-template.zip
├── template.json
├── sections/
└── assets/
    └── style.css    ← Harus ada dengan nama exact ini
```

**Note:**
- `style.css` adalah **mandatory**
- `script.js` adalah optional
- Folder `images/` adalah optional

---

### 7. **📦 ZIP appears to have an extra parent folder**

**Error Message:**
```
ZIP appears to have an extra parent folder. Files should be in the root of the ZIP.
```

**Penyebab:**
- ZIP dibuat dengan parent folder

**Solusi:**

**❌ Wrong Way (Windows Explorer):**
```
Right-click folder → Send to → Compressed folder
Result:
my-template.zip
└── my-template/    ← Extra folder!
    ├── template.json
    └── sections/
```

**✅ Correct Way:**
1. Masuk ke dalam folder template
2. Select semua files/folders (Ctrl+A)
3. Right-click → Send to → Compressed folder
4. Atau gunakan command line:
```bash
# Windows PowerShell
Compress-Archive -Path * -DestinationPath ../my-template.zip

# Mac/Linux
zip -r ../my-template.zip *
```

---

### 8. **🍎 ZIP contains __MACOSX folder**

**Error Message:**
```
ZIP contains __MACOSX folder. Please remove this before uploading.
```

**Penyebab:**
- Mac OS otomatis menambahkan folder `__MACOSX` saat zip

**Solusi (Mac Users):**

**Option 1: Terminal**
```bash
zip -r my-template.zip * -x "*.DS_Store" -x "__MACOSX"
```

**Option 2: Clean existing ZIP**
```bash
zip -d my-template.zip "__MACOSX/*"
zip -d my-template.zip "*.DS_Store"
```

**Option 3: Use Keka or The Unarchiver**
- Download [Keka](https://www.keka.io/)
- Compress dengan Keka (tidak akan include __MACOSX)

---

### 9. **📄 Section file is not a valid text/HTML file**

**Error Message:**
```
Section file 'sections/cover.html' is not a valid text/HTML file or contains binary data.
```

**Penyebab:**
- File bukan text file (binary file)
- File corrupt
- Encoding salah

**Solusi:**
1. Pastikan file adalah HTML text file
2. Save dengan encoding **UTF-8**
3. Jangan include binary files di sections/
4. Check file dengan text editor

---

### 10. **🔒 ZIP contains unsafe paths (path traversal detected)**

**Error Message:**
```
ZIP contains unsafe paths (path traversal detected). This is a security risk.
```

**Penyebab:**
- ZIP mengandung path traversal (`../`)
- Absolute paths (`/` atau `C:\`)

**Solusi:**
1. Jangan manual edit ZIP dengan tools yang bisa inject path traversal
2. Buat ZIP dari scratch dengan cara yang benar
3. Gunakan tools resmi (Windows Explorer, Mac Finder, atau zip command)

---

## 🎨 Best Practices

### 1. **Gunakan Template Starter**
Download template starter dari dokumentasi untuk struktur yang sudah benar.

### 2. **Validasi Sebelum Upload**
```bash
# Check ZIP structure
unzip -l my-template.zip

# Should show:
# template.json
# sections/cover.html
# sections/opening.html
# assets/style.css
```

### 3. **Test Locally**
1. Extract ZIP
2. Cek semua file ada
3. Validasi `template.json` di jsonlint.com
4. Re-zip dengan benar
5. Upload

### 4. **Version Control**
Simpan template di Git untuk tracking changes:
```
my-template/
├── .git/
├── template.json
├── sections/
└── assets/
```

Build ZIP dari Git:
```bash
git archive -o my-template.zip HEAD
```

---

## 🔍 Debugging Tips

### Check ZIP Contents
```bash
# Windows PowerShell
Expand-Archive -Path my-template.zip -DestinationPath temp
tree temp /F

# Mac/Linux
unzip -l my-template.zip
```

### Validate JSON
```bash
# Using jq (Mac/Linux)
cat template.json | jq .

# Using Python
python -m json.tool template.json
```

### Check File Encoding
```bash
# Mac/Linux
file -I sections/cover.html

# Should show: text/html; charset=utf-8
```

---

## 📞 Support

Jika masih ada error setelah mengikuti guide ini:

1. **Check Logs:**
   - Admin Panel → System → Logs
   - Look for detailed error messages

2. **Documentation:**
   - `/docs/templates/TEMPLATE_CREATION_GUIDE.md`
   - `/docs/templates/QUICK_REFERENCE.md`

3. **Contact Support:**
   - Include error message
   - Attach ZIP file (if possible)
   - Screenshot dari admin panel

---

## ✅ Success Checklist

Sebelum upload, pastikan:

- [ ] `template.json` ada di root ZIP
- [ ] JSON valid (test di jsonlint.com)
- [ ] Field `name` dan `slug` ada
- [ ] Slug format valid (lowercase, hyphen only)
- [ ] Folder `sections/` ada
- [ ] Semua section files ada dan match dengan `template.json`
- [ ] File `assets/style.css` ada
- [ ] Tidak ada parent folder di ZIP
- [ ] Tidak ada `__MACOSX` folder (Mac users)
- [ ] Semua files UTF-8 encoding
- [ ] File size < 50MB

Jika semua checklist ✅, upload akan sukses! 🎉
