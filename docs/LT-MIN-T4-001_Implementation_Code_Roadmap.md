# Logistik Tracking (Minimal) — Implementation Code Roadmap

**Document ID:** LT-MIN-T4-001
**Project:** akllogistik-minimal
**Protocol:** Protokol Koding Bos Ambon
**Status:** Draft — Ready for coding

> ⚠️ **EXCLUDED** — Langkah 6 (Case Management) dan sebagian Langkah 5 (API tracking) disesuaikan. Filament diganti Blade.

---

## 1. Protokol Koding Bos Ambon

Sama seperti project lama:
1. Jangan langsung eksekusi — diskusiin dulu, baru eksekusi setelah disetujui
2. Verifikasi tiap step dengan `php artisan optimize:clear` + `php artisan migrate --force`
3. Sync file modifikasi ke `docs/` folder
4. Security: credentials [REDACTED] in all output

## 2. Peta Jalan Implementasi (Roadmap Berjenjang)

### Langkah 1: Setup Konfigurasi & Service Generator Nomor Resi (Tingkat Terendah)

```
1. mkdir akllogistik-minimal && cd akllogistik-minimal
2. composer create-project laravel/laravel . (skip dev mode)
3. Install packages: dompdf, intervention/image, phpredis, league/flysystem-s3-v3
4. Buat .env (APP_KEY, DB, REDIS, MINIO, SESSION_DRIVER)
5. Dockerfile (php:8.3-fpm-alpine, oniguruma-dev, pcntl)
6. docker-compose.yml (postgres, redis, minio, app, nginx) — NO worker
7. ServiceGenerator: ResiGenerator (TRK), ManifestGenerator (MNF), SealGenerator (SEAL)
8. Bootstrapper: UUID trait, HasUuid trait, BranchScope trait
```

### Langkah 2: Migrasi Database & Model (7 Tabel)

```
Migration order:
1. users (dengan UUID PK, role enum, phone_number, branch_id FK)
2. branches (+ region_code columns)
3. regions (code, name, level, parent_code)
4. shipments (+ origin/destination branch_id)
5. packages (FK shipment_id)
6. manifests (+ origin/destination branch_id, seal_number)
7. manifest_items (FK manifest_id, shipment_id)
8. tracking_events (FK shipment_id, event_type, actor_id)
9. audit_logs (actor_id FK, action_type CHECK constraint)

Models:
- User (HasFactory, HasUuid, Authenticatable)
- Branch (HasUuid, BranchScope optional)
- Region (self-referencing parent)
- Shipment (HasUuid, BranchScope, origin/destination branches)
- Package (HasUuid, FK shipment)
- Manifest (HasUuid, BranchScope)
- ManifestItem (HasUuid, FK manifest + shipment)
- TrackingEvent (HasUuid, FK shipment)
- AuditLog (HasUuid, FK user)
```

> ⚠️ **EXCLUDED tables:** cases, case_evidence, case_decisions, case_related, case_status_history, tracking_event_timings, sla_engine_jobs, decision_review, audit_login_sessions, audit_login_sessions — NOT created.

### Modul Tambahan: Manifest Keberangkatan Armada & Manifest Items
- Manifest dispatch: auto-generate seal_number `SEAL-{manifest_no}` if empty
- Manifest items: assign shipments ke manifest (exclude yang sudah ada manifest aktif)

### Langkah 3: Keamanan, Autentikasi & Pembatasan Sesi

```
1. Sanctum setup (publish migration, tokenable_id = UUID)
2. AuthController (login, logout)
3. Role middleware (check auth user role)
4. BranchScope global scope di Shipment, Manifest
5. Session concurrent limit (configurable)
6. AuditLoginListener (Login event → AuditLog::create)
```

### Langkah 4: Core Logistics — Shipment Intake, Manifest Dispatch & POD Upload

```
1. ShipmentController: index, create, show, edit (blade views)
2. ManifestController: index, create, show, dispatch, receive
3. Package CRUD (nested di shipment form)
4. TrackingEvent: auto-create on status change
5. AdjustKoliRequest (Staf+, before departure only)
6. UploadPodRequest (Staf+, MinIO upload)
7. RegionController: cascading select endpoints
```

### Langkah 5: Public Tracking API (Zero PII Transformer)

```
1. TrackingController@search (public, zero PII)
2. Route: GET /api/tracking/{resi}
3. Route: POST /api/tracking/search
4. Transformer: strip sender/receiver phone/address
5. CORS policy untuk customer access
```

> ⚠️ **EXCLUDED:** `/api/cases/*` endpoints — lihat project lama Langkah 6.

### Langkah 6 (EXCLUDED): Case Management Lifecycle

> ⚠️ **THIS ENTIRE STEP IS EXCLUDED.** Tidak ada Case Management. Lihat project lama LT-T4-001 untuk full implementation steps. Case service, controller, views, seeder, migration semua tidak dibuat.

## 3. Detail Berkas Per Langkah Kerja

| Langkah | File utama yang perlu dibuat |
|---|---|
| 1 | `Dockerfile`, `.env`, `docker-compose.yml`, `app/Services/ResiGenerator.php`, `app/Services/ManifestGenerator.php`, `app/Services/SealGenerator.php`, `app/Traits/HasUuid.php`, `app/Scopes/BranchScope.php` |
| 2 | 9 migration files, 9 Model files |
| 3 | `app/Models/User.php` (update), Sanctum migration, `app/Http/Controllers/AuthController.php`, `app/Http/Middleware/CheckRole.php`, `app/Listeners/AuditLoginListener.php` |
| 4 | `app/Http/Controllers/ShipmentController.php`, `app/Http/Controllers/ManifestController.php`, `app/Http/Controllers/RegionController.php`, 4 FormRequests (AdjustKoli, UploadPod, DispatchManifest, ReceiveManifest), routes |
| 5 | `app/Http/Controllers/Api/TrackingController.php`, route group `api/tracking`, Zero PII transformer |
| 6 | `app/Services/CaseManagementService.php`, `app/Http/Controllers/Api/CaseController.php`, CaseResource, Case migrations |

> ⚠️ **EXCLUDED files:** semua file di Langkah 6 kolom kanan — TIDAK dibuat di project minimal.

---

**End of document — LT-MIN-T4-001 v1.0-draft (Ready for coding)**
