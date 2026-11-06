# Logistik Tracking (Minimal) — CP-006: Project Setup Baseline

**Checkpoint ID:** LT-MIN-CP-006
**Date:** 2026-10-28
**Status:** Active — Implementation Ready
**PIC:** @ambon

---

## What's Done

1. ✅ Project folder created: `/home/ambon/akllogistik-minimal/`
2. ✅ Documentation structure: 7 docs created in `docs/`
3. ✅ PRD following same structure as `LT-RA-PRD-001_Readiness_Assessment.md`
4. ✅ Technical Architecture following `LT-T3-001` structure
5. ✅ Implementation Roadmap following `LT-T4-001` structure
6. ✅ Frontend (Blade) Architecture following `LT-T5-001` structure
7. ✅ Runbook/API Manual following `LT-RUNBOOK-001` structure
8. ✅ Decision Checkpoint Register created
9. ✅ Excluded features clearly marked ⚠️ EXCLUDED in every doc
10. ✅ Docker Compose strategy: php-fpm + postgres + redis + minio + nginx (NO worker)

## Excluded Features (NOT to be implemented)

- ❌ Case Management (cases, evidence, decisions, status history)
- ❌ Filament v3 (Resources, Pages, Widgets, Actions)
- ❌ Filament admin panel
- ❌ Redis queue worker (queue_connection = sync)
- ❌ SLA engine / case tracking timing
- ❌ Decision review workflow
- ❌ Loss decree print template (SK — Surat Keputusan)

## DB Tables (7 core + supporting)

| Table | Count |
|---|---|
| users | 1 |
| branches | 1 |
| regions | 1 |
| shipments | 1 |
| packages | 1 |
| manifests | 1 |
| manifest_items | 1 |
| tracking_events | 1 |
| audit_logs | 1 |
| **Total** | **9** |

## Excluded Tables

| Table | Reason |
|---|---|
| cases | Case Management excluded |
| case_evidence | Case Management excluded |
| case_decisions | Case Management excluded |
| case_related | Case Management excluded |
| case_status_history | Case Management excluded |
| tracking_event_timings | SLA engine excluded |
| sla_engine_jobs | SLA engine excluded |
| decision_review | Case Management excluded |
| audit_login_sessions | Not needed — login event only |

## Next Actions

- [ ] Langkah 1: Docker setup + composer create-project
- [ ] Langkah 2: Migrations + Models
- [ ] Langkah 3: Auth + RBAC + BranchScope
- [ ] Langkah 4-7: Controllers + Blade views + API

## Docker Compose Strategy

```
services:
  postgres:15 (port 5432, db: logistik_min)
  redis:7 (port 6379, cache only)
  minio:latest (S3 gateway, bucket: logistik-tracking)
  app: php:8.3-fpm-alpine (phpredis, dompdf, intervention)
  nginx: (port 8000, fastcgi to app:9000)
  # NO worker container
```

---

**End of document — LT-MIN-CP-006 (Active)**
