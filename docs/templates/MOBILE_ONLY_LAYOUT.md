# 📱 Mobile-Only Layout Guide

## Overview

All MyAkad templates now use a **mobile-only layout** (fixed 480px width on all devices). This ensures consistent display across desktop, tablet, and mobile devices.

---

## ✅ Current Implementation

The mobile-only layout is **ACTIVE** and configured in `/public/css/template-base.css`.

### Key Features

- **Fixed Width**: All devices display templates at 480px width
- **Centered Layout**: Template is centered with gray background on desktop
- **No Responsive Breakpoints**: Tailwind responsive classes (md:*, lg:*) are overridden
- **Touch Optimized**: Buttons and interactive elements optimized for mobile

---

## 🎨 How It Works

### 1. Fixed Container Width

```css
body {
  max-width: 480px !important;
  margin: 0 auto !important;
  background: #f5f5f5 !important;
}

.container {
  max-width: 480px !important;
}
```

### 2. Override Responsive Tailwind Classes

```css
@media (min-width: 768px) {
  /* Force single column grid */
  .md\:grid-cols-2,
  .md\:grid-cols-3,
  .md\:grid-cols-4 {
    grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
  }
  
  /* Force column flex direction */
  .md\:flex-row {
    flex-direction: column !important;
  }
  
  /* Keep mobile font sizes */
  .md\:text-5xl {
    font-size: 2.25rem !important;
  }
}
```

### 3. Prevent Horizontal Scroll

```css
html, body {
  overflow-x: hidden !important;
}
```

---

## 📐 Layout Specifications

| Property | Value | Description |
|----------|-------|-------------|
| Max Width | 480px | Fixed width for all devices |
| Background | #f5f5f5 | Gray background on desktop |
| Padding | 1rem | Container horizontal padding |
| Overflow | hidden | Prevent horizontal scroll |

---

## 🔧 Customization

### Change Mobile Width

Edit `/public/css/template-base.css`:

```css
/* Options: 375px (iPhone SE), 390px (iPhone), 412px (Android), 480px (Comfortable) */
body {
  max-width: 480px !important; /* Change this value */
}
```

### Disable Mobile-Only Layout

To revert to responsive design, comment out the mobile-only section in `template-base.css`:

```css
/* ============================================
   MOBILE-ONLY LAYOUT CONFIGURATION
   ============================================ */

/* Uncomment to disable mobile-only layout
body {
  max-width: 480px !important;
  ...
}
*/
```

---

## 🎯 Template Development Guidelines

### DO ✅

- Use Tailwind utility classes as normal
- Design for 480px width
- Test on mobile devices
- Use touch-friendly button sizes (min 44x44px)

### DON'T ❌

- Don't add responsive breakpoints (md:, lg:, xl:)
- Don't use fixed widths wider than 480px
- Don't rely on hover states (use :active instead)
- Don't use horizontal scroll

---

## 📱 Device Display

### Desktop (1920x1080)
```
┌─────────────────────────────────────┐
│         Gray Background             │
│  ┌───────────────────┐              │
│  │   Template        │              │
│  │   (480px)         │              │
│  │                   │              │
│  └───────────────────┘              │
│                                     │
└─────────────────────────────────────┘
```

### Tablet (768x1024)
```
┌──────────────────────┐
│   Gray Background    │
│ ┌──────────────────┐ │
│ │   Template       │ │
│ │   (480px)        │ │
│ └──────────────────┘ │
└──────────────────────┘
```

### Mobile (375x667)
```
┌──────────────┐
│   Template   │
│   (375px)    │
│   Full Width │
└──────────────┘
```

---

## 🧪 Testing Checklist

- [ ] Desktop: Template centered with gray background
- [ ] Tablet: Template centered with gray background
- [ ] Mobile: Template fills screen width
- [ ] No horizontal scroll on any device
- [ ] All content visible and readable
- [ ] Buttons and links are touch-friendly
- [ ] Images scale properly
- [ ] Text is legible at mobile size

---

## 🚀 Benefits

### For Users
- ✅ Consistent experience across all devices
- ✅ Optimized for mobile viewing
- ✅ Fast loading (no responsive CSS overhead)
- ✅ Touch-friendly interface

### For Developers
- ✅ Single layout to maintain
- ✅ Simpler CSS (no breakpoints)
- ✅ Easier testing (one size)
- ✅ Faster development

---

## 📝 Notes

- Mobile-only layout is applied globally via `template-base.css`
- No changes needed in individual template files
- Layout is enforced with `!important` to override Tailwind
- Cache buster (`?v=timestamp`) ensures CSS updates are loaded

---

## 🔗 Related Documentation

- [Template Creation Guide](./TEMPLATE_CREATION_GUIDE.md)
- [Template Base CSS](../../public/css/template-base.css)
- [Template Base JS](../../public/js/template-base.js)

---

**Version:** 1.0.0  
**Last Updated:** May 11, 2026  
**Status:** ✅ ACTIVE
