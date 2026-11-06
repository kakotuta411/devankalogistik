# Decision Checkpoint Register — akllogistik-minimal

**Document ID:** LT-MIN-DECISION-REGISTER-001
**Project:** akllogistik-minimal
**Status:** Open items carried to implementation

> ⚠️ **EXCLUDED** — Semua keputusan terkait Case Management (LT-RA-065B, LT-RA-066A, LT-RA-067, LT-RA-068, LT-RA-070, LT-RA-071 §18.6) tidak berlaku. Hanya keputusan Core Logistics Tracking yang berlaku.

---

## Confirmed Decisions (Applies to Minimal)

| ID | Decision | Source | Impact |
|---|---|---|---|
| D-001 | Resi format: `TRK-{YYYYMMDD}-{SEQ:5}` | LT-RA-PRD §5 | Shipment model + ResiGenerator |
| D-002 | Manifest format: `MNF-{YYYYMMDD}-{SEQ:5}` | LT-T3-001 §4 | Manifest model + ManifestGenerator |
| D-003 | Seal number: `SEAL-{manifest_no}` auto | LT-T4 | ManifestService::dispatchManifest |
| D-004 | UUID primary keys everywhere | LT-T3 §4 | All models use uuid PK |
| D-005 | BranchScope di booted() | LT-T3 §6.2 | Shipment + Manifest models |
| D-006 | Role: Admin, Head_of_Branch, Leader_Supervisor, Executive_Viewer, Staf | LT-RA-PRD §7 | User role enum |
| D-007 | Direktur/Executive_Viewer read-only, 403 on write | LT-RA-PRD §7.4 | Middleware + controller |
| D-008 | Public tracking = zero PII | LT-T3 §5.1 | TrackingController transformer |
| D-009 | Region sync: emsifa.com, cron 02:00 WIB | LT-T3 §4 | SyncRegionsCommand + console.php |
| D-010 | Audit trail: login event + CRUD log | LT-RA-PRD §12 | AuditLoginListener + service call |
| D-011 | Docker Compose v2, php-fpm-alpine | User preference | docker-compose.yml |
| D-012 | No Filament — Laravel Blade only | User preference | Frontend architecture LT-MIN-T5-001 |
| D-013 | No Case Management — excluded | User preference | NOT implemented |

## EXCLUDED — Not applicable to minimal

| Decision ID | Original Source | Why excluded |
|---|---|---|
| D-CM-001 | LT-RA-065B: Admin buat case Open | Case Management excluded |
| D-CM-002 | LT-RA-066A: Keputusan oleh otoritas | Case Management excluded |
| D-CM-003 | LT-RA-067: Status transisi matriks | Case Management excluded |
| D-CM-004 | LT-RA-068: Hak akses detail peraksi | Case Management scope — hanya shipment/manifest/branch berlaku |
| D-CM-005 | LT-RA-070: Resolved vs Closed prinsip | Case Management excluded |
| D-CM-006 | GAP-001–012: SLA, severity, eskalasi | Case Management excluded |
| D-CM-007 | Draft decision review process | Case Management excluded |

## Open/GAP — carried forward

| ID | Isu | Status |
|---|---|---|
| GAP-MIN-001 | Notification system | Open — belum dirancang |
| GAP-MIN-002 | Auto route suggestion | Open — tidak diimplement |
| GAP-MIN-003 | SLA tracking event threshold | Open — optional enhancement |
| GAP-MIN-004 | Pencarian resi via QR | Open — depend on barcode scanner availability |

---

**End of document — Decision Register (active)**
