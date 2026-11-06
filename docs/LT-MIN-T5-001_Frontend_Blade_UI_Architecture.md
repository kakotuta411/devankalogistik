# Logistik Tracking (Minimal) — Frontend Architecture (Blade)

**Document ID:** LT-MIN-T5-001
**Project:** akllogistik-minimal
**UI Framework:** Laravel Blade (NO Filament)
**Status:** Draft

> ⚠️ **EXCLUDED** — Filament v3 panel, Resources, Pages, Widgets, Actions, Relations, Infolists, forms. Semua diganti Blade native. Lihat project lama `LT-T5-001` untuk Filament architecture.

---

## 1. Prinsip & Batasan Ketat Tahap 5 (Non-Negotiable)

1. **No Filament** — gunakan Laravel + Blade + Alpine.js (jika butuh interaktivitas)
2. **Responsive** — Bootstrap CDN (lightweight) atau Tailwind CDN (tanpa build step kompleks)
3. **Auth page terpisah** — login tidak pakai Filament panel
4. **Print views terpisah** — dedicated blade templates untuk PDF

## 2. Struktur Frontend (Blade Layout)

```
resources/views/
├── layouts/
│   └── app.blade.php     — master layout (header, sidebar, footer)
├── auth/
│   ├── login.blade.php
│   └── register.blade.php (jika diperlukan)
├── dashboard.blade.php   — overview stats (shipments today, manifests, etc.)
├── shipments/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php (with tracking timeline)
│   ├── edit.blade.php
│   └── print/
│       └── label.blade.php
├── manifests/
│   ├── index.blade.php
│   ├── create.blade.php (with shipment assigner)
│   ├── show.blade.php (with dispatch/receive buttons)
│   └── print/
│       └── sheet.blade.php
├── branches/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
├── users/
│   ├── index.blade.php
│   └── edit.blade.php
├── regions/
│   └── select-cascading.blade.php (province→regency→district→village)
├── print/
│   ├── shipment-list.blade.php
│   ├── manifest-list.blade.php
│   └── template-management.blade.php (logo upload + settings)
└── components/
    ├── navbar.blade.php
    ├── sidebar.blade.php
    └── tracking-timeline.blade.php
```

## 3. UI Komponen Mapping (Filament → Blade)

| Filament Component | Blade Equivalent |
|---|---|
| Filament::resource(ShipmentResource) | ShipmentController + blade CRUD views |
| Filament::page(Dashboard) | dashboard.blade.php |
| Filament::widget(StatsOverview) | Dashboard stat cards (blade count) |
| Filament::page(TemplateManagement) | PrintSettingController + template-management.blade.php |
| Filament::form (Select cascading) | Select2 / native dropdown + AJAX |
| Filament::table (bulkActions) | Manual checkbox + JS |
| ViewAction slideOver | Modal popup (Blade + Alpine.js) |
| Export PDF header action | Direct link to print route |

## 4. Blade Layout Structure

### layouts/app.blade.php
```html
<html>
<head>
    <title>{{ config('app.name') }}</title>
    <!-- Tailwind CDN -->
    <!-- Alpine.js CDN -->
</head>
<body class="bg-gray-50">
    @include('components.navbar')
    <div class="flex">
        @include('components.sidebar')
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
```

## 5. Print Settings UI

- Route: `/admin/template-management` (Admin only)
- Form: logo upload (intervention/image validation)
- Inputs: company_name, header_text, footer_text, paper_format (A4/Letter/Thermal)
- Save: JSON to `storage/app/print_settings.json`
- ViewComposer: `PrintSettingComposer` → share `$logoUrl`, `$printSettings` to `print.*` views

---

**End of document — LT-MIN-T5-001 v1.0-draft (Blade frontend, no Filament)**
