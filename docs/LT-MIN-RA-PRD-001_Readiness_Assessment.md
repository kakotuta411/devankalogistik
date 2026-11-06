# Logistik Tracking (Minimal) — Readiness Assessment & PRD

**Document ID:** LT-MIN-RA-PRD-001
**Project:** akllogistik-minimal
**Framework:** Laravel 11, PHP 8.3
**UI:** Laravel Blade (NO Filament)
**Database:** PostgreSQL 15
**Scope:** Core Logistics Tracking Only — Case Management EXCLUDED
**Version:** 1.0-draft
**Language:** Bahasa Indonesia
**PIC:** @ambon

> ⚠️ **EXCLUDED** — Bagian Case Management (§6, §8, §9, §10, §11, §13) dihapus dari versi minimal. Lihat `LT-RA-PRD-001_Readiness_Assessment.md` di project lama untuk full spec. Semua referensi ke Filament diganti Laravel Blade.

---

## 1. Tujuan Dokumen

Dokumen ini mendefinisikan kesiapan konseptual untuk **Logistik Tracking Minimal** — versi simplified tanpa Case Management dan tanpa Filament. Fokus: shipment tracking, manifest antar cabang, regional data sync, dan template print.

## 2. Posisi Tahap 1 dalam Proyek

Proyek ini merupakan fork/potongan dari project `akllogistik` full yang sudah berjalan. Tahap 1 = kesiapan pemahaman bisnis dan kesepakatan narasi.

## 3. Prinsip Kerja Dokumentasi

- Setiap bab menghasilkan `.md` mandiri + Decision Checkpoint Register
- Versi dan status dokumen jelas
- Pisahkan `Confirmed`, `Recommended`, `Open/GAP`, `Excluded`
- Jika konflik dengan project lama, catat dan minta konfirmasi

## 4. Ringkasan Produk dan Konteks Bisnis

Perusahaan logistik/3PL. Sistem mencatat riwayat tracking, pencarian resi, manifest antar cabang, sync wilayah Indonesia.

> ⚠️ **EXCLUDED:** Case Management (investigasi, eskalasi, keputusan, penutupan, SLA, audit trail kasus). Lihat project lama §6.

### 4.1 Batas ruang lingkup umum

**Dalam ruang lingkup:** riwayat tracking terpusat, pencatatan status/pergerakan, pencarian resi, validasi QR, pengelolaan koli, manifest dispatch/receive, sync wilayah, print template, audit login + CRUD.

> ⚠️ **EXCLUDED:** Case Management, eskalasi, keputusan bisnis, rekomendasi rute otomatis, mengganti SOP operasional. Lihat project lama §4.1.

## 5. Prinsip Domain Tracking yang Telah Dicatat

- Satu resi = satu shipment/pengiriman
- Satu shipment dapat terdiri dari beberapa koli
- `package_id` = identitas internal permanen
- `package_code` = referensi pencarian/validasi
- Perubahan jumlah koli hanya sebelum keberangkatan, oleh Admin cabang asal

> ⚠️ **EXCLUDED:** Prinsip khusus Case Management tidak berlaku.

## 6. Case Management — Tujuan dan Batasan

> ⚠️ **EXCLUDED** — Fitur ini TIDAK termasuk dalam versi minimal. Lihat project lama `LT-RA-PRD-001_Readiness_Assessment.md` §6 untuk full spesifikasi. Semua tabel kasus, workflow keputusan, SLA, kategori, dan eskalasi tidak diimplementasikan di project ini.

## 7. Peran dan Kewenangan Konseptual

### 7.1 Admin Operasional
- Full akses semua fitur (shipment, manifest, branch, user)
- Dapat CRUD semua resource

### 7.2 Leader / Supervisor
- Read/write cabangnya
- View kasus dari cabangnya (EXCLUDED — tidak ada case management)

> ⚠️ **EXCLUDED:** Kewenangan khusus Case Management tidak berlaku.

### 7.3 Head of Branch / Head of Warehouse
- Full akses shipment/manifest cabangnya
- Bisa dispatch/receive manifest cabang sendiri

> ⚠️ **EXCLUDED:** Kewenangan keputusan kasus Critical tidak berlaku.

### 7.4 Status kewenangan setelah LT-RA-068

> ⚠️ **EXCLUDED** — Referensi ke keputusan LT-RA-068 khusus Case Management tidak berlaku. Hanya berlaku untuk shipment/manifest/branch/user management.

## 8. Standar Status Kasus

> ⚠️ **EXCLUDED** — Tidak ada status kasus. Lihat project lama §8. Hanya berlaku shipment statuses (Created, In Transit, Delivered, dll.).

## 9. SLA — Standar yang Dikonfirmasi

> ⚠️ **EXCLUDED** — Tidak ada SLA Case Management. Tracking event SLA (15 menit) mungkin diterapkan di masa depan, belum di project ini.

## 10. Kategori Kasus

> ⚠️ **EXCLUDED** — Tidak ada kategori kasus di project minimal.

## 11. Model Relasi Insiden dan Kasus — Model B

> ⚠️ **EXCLUDED** — Tidak ada sistem kasus.

## 12. Bukti dan Audit Trail

### 12.1 Audit Trail (terbatas)
- Login/logout event tercatat (AuditLoginListener)
- CRUD shipment, manifest, branch, user tercatat — actor_id, action, ip, user_agent, details JSON
- Tidak ada audit khusus Case Management

> ⚠️ **EXCLUDED:** Bukti kasus, kronologi, eskalasi-proof tidak berlaku.

## 13. Aturan Transisi Status — Keputusan LT-RA-067

> ⚠️ **EXCLUDED** — Matriks status kasus tidak berlaku. Hanya berlaku shipment status transitions (see T3 §4).

## 14. Keputusan, Rekomendasi, dan Gap

### 14.1 Keputusan yang telah dikonfirmasi (filter minimal)
- Core Logistics Tracking — YES
- Case Management — EXCLUDED
- Shipment statuses — Confirmed
- Manifest workflow (draft → dispatched → received → completed) — Confirmed
- Branch scope for staff — Confirmed
- Public tracking (zero PII) — Confirmed

### 14.2 Rekomendasi yang belum tentu final
- SLA tracking event 15 menit — belum final
- Auto route suggestion — belum final
- Notification system — belum final

### 14.3 Open/GAP register ringkas
- GAP-MIN-001: Notification system detail
- GAP-MIN-002: Auto route suggestion
- GAP-MIN-003: SLA tracking event threshold

## 15. Dependensi dan Risiko

- Risiko: regional data sync API (emsifa.com) bisa down → implement async retry
- Risiko: POD upload ke MinIO → gunakan queue (Redis) backup local

## 16. Checkpoint Terakhir dan Langkah Selanjutnya

- **Checkpoint 1:** PRD ini — Ready for execution
- **Langkah selanjutnya:** Technical Architecture (T3), Implementation Roadmap (T4)

## 17. Instruksi untuk Agent/Developer Berikutnya

1. Baca dokumen ini + LT-MIN-T3-001 + LT-MIN-T4-001
2. Jangan implement Case Management — semua referensi §6, §8, §9, §10, §11, §13 EXCLUDED
3. Jangan gunakan Filament — pakai Laravel Blade
4. Gunakan Docker Compose dengan php-fpm + postgres + redis + minio + nginx (NO worker container)

---

**End of document — LT-MIN-RA-PRD-001 v1.0-draft (Ready for execution)**
