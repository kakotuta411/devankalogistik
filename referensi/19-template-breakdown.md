# Template Dokumen — Breakdown File & Data

> Last Updated: 2026-11-10

---

## 1. Cara Kerja Template

- **Setting global per jenis dokumen** disimpan di `storage/app/public/print_settings.json`
- **Composer** (`app/View/Composers/PrintSettingComposer.php`) menginjeksi 2 variabel ke SEMUA view `print.*`:
  - `$printSettings` → array: `company_name`, `header_text`, `footer_text`, `paper_format`, `company_logo`
  - `$logoUrl` → base64/URL logo (fallback ke `public/images/logo.png`)
- **UI setting**: `/admin/template-management` → `PrintSettingController`
- **3 jenis dokumen**: `invoice`, `manifest`, `shipment` (resi label)

> ⚠️ Catatan: `resi-detail.blade.php` TIDAK pakai `$printSettings` (nama perusahaan masih hardcoded "PT. DEVANKA LOGISTIK INDONESIA").

---

## 2. File yang Bisa Diedit

### A. Setting & Controller
| File | Fungsi |
|---|---|
| `app/Http/Controllers/PrintSettingController.php` | Load/save setting (`loadAllSettings`, `store`) |
| `app/View/Composers/PrintSettingComposer.php` | Inject `$printSettings` + `$logoUrl` ke view |
| `app/Providers/AppServiceProvider.php` | Daftar composer (`View::composer('print.*', ...)`) |
| `resources/views/template-management.blade.php` | UI halaman setting |
| `storage/app/public/print_settings.json` | Data setting (JSON, bisa diedit manual) |

### B. Template Blade per Dokumen
| Dokumen | File Blade | Route Print | Ukuran Kertas |
|---|---|---|---|
| Resi detail | `print/resi-detail.blade.php` | `/print/shipments/{id}/resi` | 9.5×5.5in (684×396pt) |
| Koli label | `print/resi-label.blade.php` | `/print/shipments/{id}/label` | **4×6in (288×432pt)** — lihat catatan dompdf |
| Invoice | `print/invoice.blade.php` | `/print/invoices/{id}/pdf` | A4 (sesuai `paper_format`) |
| Manifest sheet | `print/manifest-sheet.blade.php` | `/print/manifests/{id}/sheet` | A4 (sesuai `paper_format`) |
| Daftar shipment (export) | `print/shipment-list.blade.php` | `/print/shipments/list` | A4 |
| Daftar invoice (export) | `print/invoice-list.blade.php` | `/invoices/export/pdf` | A4 |
| Daftar manifest (export) | `print/manifest-list.blade.php` | `/manifests/export/pdf` | A4 |
| Daftar PO (export) | `print/purchase-order-list.blade.php` | `/purchase-orders/export/pdf` | A4 |

Folder `print/custom/` kosong — disediakan untuk override template custom (belum dipakai).

---

## 3. Data yang Bisa Ditarik per Dokumen

### Umum (semua print view)
```
$printSettings['company_name']   // nama perusahaan
$printSettings['header_text']    // teks header
$printSettings['footer_text']    // teks footer
$printSettings['paper_format']   // A4 | Letter | thermal | dotmatrix-half
$printSettings['company_logo']   // path logo
$logoUrl                         // base64/URL logo siap pakai
```

### A. Resi Detail (`$shipment`)
| Field | Keterangan |
|---|---|
| `resi_no` | No resi |
| `po_number` | No PO |
| `created_at` | Tanggal masuk |
| `shipment_status` | Status pengiriman |
| `service_type` | Jenis layanan (REGULAR/EXPRESS) |
| `total_koli` | Total koli |
| `total_weight` | Total berat (kg) |
| `package_description` | Isi barang |
| `payment_type` | Tipe pembayaran |
| `sender_name`, `sender_phone`, `sender_address` | Data pengirim |
| `sender_province_code`, `sender_regency_code`, `sender_district_code`, `sender_village_code` | Region pengirim |
| `receiver_name`, `receiver_phone`, `receiver_address` | Data penerima |
| `receiver_province_code`, `receiver_regency_code`, `receiver_district_code`, `receiver_village_code` | Region penerima |
| `originBranch`, `destinationBranch`, `customer` | Relasi |

### B. Koli Label (`$shipment` + `$barcodeBase64`)
- Semua field `$shipment` seperti di atas
- `$barcodeBase64` → barcode Code 128 dari `resi_no` (GD)
- `originBranch->city`, `destinationBranch->city` → route
- Nama region: `\App\Models\Region::where('code', ...)->value('name')`
- Loop `@for($koli = 1; $koli <= $shipment->total_koli; $koli++)` → label per koli

### C. Invoice (`$invoice`)
| Field | Keterangan |
|---|---|
| `invoice_no` | No invoice |
| `invoice_date`, `due_date` | Tanggal invoice & jatuh tempo |
| `customer_name`, `customer_phone`, `customer_address`, `customer_email` | Data customer |
| `customer_province_code`, `customer_regency_code`, `customer_district_code`, `customer_village_code` | Region customer |
| `invoice_status` | Status (Draft/Paid/dsb) |
| `branch->name` | Cabang |
| `items` | Koleksi item invoice |
| `items[*]->shipment->resi_no` | No resi per item |
| `items[*]->shipment->originBranch->city` | Asal |
| `items[*]->shipment->destinationBranch->city` | Tujuan |
| `items[*]->shipment->total_koli`, `total_weight`, `service_type` | Detail |
| `subtotal`, `shipping_cost`, `discount` | Angka |
| `grandTotal()` | Total akhir (method) |
| `notes` | Catatan |
| `payments` | Riwayat pembayaran (relasi) |

### D. Manifest Sheet (`$manifest`)
| Field | Keterangan |
|---|---|
| `manifest_no` | No manifest |
| `dispatch_time`, `receive_time` | Waktu muat/terima |
| `vehicle_no`, `driver_name` | Kendaraan & supir |
| `manifest_status` | Status |
| `originBranch->city`, `destinationBranch->city`, `destinationBranch->name` | Route |
| `items` | Koleksi item manifest |
| `items[*]->shipment->resi_no`, `service_type`, `package_description`, `total_koli`, `receiver_name`, `destinationBranch->city` | Detail |

### E. Export List
| View | Data |
|---|---|
| `shipment-list` | `resi_no`, `originBranch->city`, `destinationBranch->city`, `total_koli`, `total_weight`, `shipment_status`, `generated_at` |
| `invoice-list` | `invoice_no`, `customer_name`, `branch->city`, `invoice_status`, `subtotal`, `created_at` |
| `manifest-list` | `manifest_no`, `originBranch->city`, `destinationBranch->city`, `manifest_status`, `vehicle_no`, `driver_name`, `created_at` |
| `purchase-order-list` | `po_number`, `customer->company_name/name`, `total_amount`, `paid_amount`, `getRemainingAmount()`, `status`, `created_at` |

---

## 4. Catatan Teknis

- **Region**: bisa ambil nama via `\App\Models\Region::where('code', $code)->value('name')`
- **Paper format**: `paper_format` bisa `A4`, `Letter`, `thermal` (10×10cm), `dotmatrix-half` (241×140mm)
- **Logo**: upload via UI, tersimpan di `storage/app/public/logos/`
- **Route death**: `/admin/template-management/upload` (method `uploadTemplate`) TIDAK ADA di controller — jangan dipakai
- **Custom template**: bisa taruh file di `print/custom/` lalu load via `Pdf::loadView('print.custom.nama')` (perlu koding)

---

## 4b. Catatan dompdf (PENTING — layout PDF)

> dompdf v3.1.6. **Browser preview bohong** — flexbox/max-height jalan di browser tapi TIDAK di dompdf. Selalu verifikasi PDF hasil render.

| Quirk | Dampak | Solusi |
|---|---|---|
| `justify-content` / flex distribution | Diabaikan → konten numpuk di atas, gap bawah | Pakai `margin-bottom` eksplisit per blok, atau `position: absolute` |
| `height: 100%` di `html, body` | Tidak berfungsi | Set `height` eksplisit di wrapper (mis. `412pt`) |
| `<img>` abaikan `max-height`/`max-width` | Render ukuran asli → overflow / tambah halaman | Set `height: 30pt; width: auto` eksplisit |
| `@page { size }` di Blade | **Mengalahkan** `setPaper()` di controller | Cek `/MediaBox` di PDF (288×432 = 4×6in, bukan 10×10cm) |
| Overflow | Tambah halaman diam-diam | Cek jumlah halaman setelah tiap perubahan |

**Pola label tinggi-tetap (dompdf-safe):**
```css
@page { size: 4in 6in; margin: 0; }
body { margin:0; padding: 0.2cm; box-sizing: border-box; }
.wrap { position: relative; border: 1px solid #000; box-sizing: border-box; height: 412pt; padding: 3px; }
.footer { position: absolute; left:0; right:0; bottom: 14pt; text-align:center; }
```

**Verify loop:** render via tinker → cek `pages` count → cek `/MediaBox` → kirim PDF ke user untuk konfirmasi visual.

> ⚠️ Setelah edit Blade yang dipakai PDF, jalankan `php artisan view:clear` (view cache bisa bikin perubahan tak terlihat).

---

## 5. Catatan Perhitungan Invoice

- `total_layanan` = (shipping_cost × total_weight) + packaging_cost + equipment_charge + other_costs
- `discount` = total_layanan × (discount_value / 100)
- `ppn` = total_layanan × (ppn / 100)
- `pph` = total_layanan × (pph / 100)
- `grandTotal()` = total_layanan − discount + ppftz03 + ppn + pph
- `displayTotal()` = `total_amount` kalau child invoice (ada `parent_invoice_id`), else `grandTotal()`
- Child invoice: kolom biaya disalin dari parent, tapi `total_amount` = sisa → tampilan pakai `displayTotal()`
