# MyAkad Template - Quick Reference Card

## 📁 Minimal Structure

```
my-template/
├── template.json          ✅ REQUIRED
├── assets/                Optional, template-owned CSS/JS/images/fonts
│   ├── style.css
│   └── script.js
└── sections/
    └── hero.html         ✅ REQUIRED (min 1)
```

## 📝 Minimal template.json

```json
{
  "name": "My Template",
  "slug": "my-template",
  "sections": [{"file": "hero.html", "label": "Hero"}],
  "assets": {
    "css": ["style.css"],
    "js": ["script.js"]
  },
  "defaults": {
    "bride_name": "Ayu",
    "groom_name": "Raka"
  }
}
```

## 🎨 Common Variables

```blade
{{ $bride_name ?? '' }}
{{ $groom_name ?? '' }}
{{ $event_date ?? '' }}
{{ $akad_venue ?? '' }}
{{ $reception_venue ?? '' }}
{{ $cover_photo_url ?? '' }}
{{ $love_story ?? '' }}
{{ $bank_name ?? '' }}
{{ $account_number ?? '' }}
```

## 🔄 Blade Syntax

### Output
```blade
{{ $variable ?? '' }}
{{ $variable ?? 'default' }}
```

### Conditionals
```blade
@if($variable)
  <p>{{ $variable ?? '' }}</p>
@endif
```

### Loops
```blade
@foreach($gallery ?? [] as $item)
  <img src="{{ $item['url'] ?? '' }}">
@endforeach
```

## 📦 Create ZIP

### Windows
```cmd
cd my-template
tar -a -c -f ..\my-template.zip *
```

### PowerShell
```powershell
Compress-Archive -Path "my-template\*" -DestinationPath "my-template.zip"
```

## ✅ Validation Checklist

- [ ] `template.json` exists with `name` and `slug`
- [ ] All CSS/JS needed by this template is inside `assets/`
- [ ] Preview fallback values are inside `template.json.defaults`
- [ ] All section files exist
- [ ] No path traversal (`../`, `/etc/`)
- [ ] ZIP structure correct (no parent folder)
- [ ] All variables use `{{ $var ?? '' }}`
- [ ] Tested in preview

## 🚀 Upload

1. Admin panel → Templates
2. Click "Upload Template"
3. Select ZIP file
4. Upload
5. Preview at `/templates/{slug}/render`

## 🐛 Common Errors

| Error | Solution |
|-------|----------|
| template.json not found | ZIP isi folder, bukan folder itu sendiri |
| CSS/JS not applied | Add files to `assets/` and list them in `template.json.assets` |
| Missing section files | Create files or update template.json |
| Unsafe paths | Remove `../` and absolute paths |

## 📚 Full Documentation

See: `docs/templates/TEMPLATE_CREATION_GUIDE.md`
