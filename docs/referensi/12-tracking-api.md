# Public Tracking API — WordPress Integration

## Endpoint
```
GET /api/tracking/{resi_no}
```
Base URL: `https://your-domain.com/api/tracking/{resi_no}`

## Response (200 OK)
```json
{
    "resi_no": "AKL-20261107-00001",
    "entry_date": "07/11/2026 08:30",
    "sender": "PT. Laksmi Maju Jaya",
    "receiver": "Mr. Zarkawi",
    "shipment_status": "In Transit",
    "tracking_events": [
        {
            "event_type": "Created",
            "location": "Jakarta Hub",
            "event_time": "2026-11-07T08:30:00",
            "notes": null
        },
        {
            "event_type": "Dispatched",
            "location": "Jakarta Hub",
            "event_time": "2026-11-07T10:15:00",
            "notes": "Driver: Budi"
        }
    ],
    "receiver_name": null,
    "delivered_at": null
}
```

## Response (404 Not Found)
```json
{
    "error": "Resi tidak ditemukan"
}
```

## Search Endpoint
```
GET /api/tracking/search?resi={query}
```
Minimal 3 karakter. Returns first matching resi.

## CORS
CORS diaktifkan (`Access-Control-Allow-Origin: *`). Bisa dipanggil dari domain WordPress manapun.

## Payload Fields
| Field | Type | Deskripsi |
|-------|------|-----------|
| resi_no | string | Nomor resi |
| entry_date | string | Tanggal masuk (d/m/Y H:i) |
| sender | string | Nama pengirim |
| receiver | string | Nama penerima |
| shipment_status | string | Created / In Transit / Delivered / Cancelled |
| tracking_events | array | Timeline tracking (event_type, location, event_time, notes) |
| receiver_name | string/null | Nama penerima (dari data shipment, hanya jika Delivered) |
| delivered_at | string/null | Tanggal diterima (hanya jika Delivered) |

## HTML + JavaScript untuk WordPress

```html
<!-- Form tracking -->
<div id="tracking-form">
    <input type="text" id="resi-input" placeholder="Masukkan nomor resi" />
    <button onclick="trackResi()">Cari</button>
</div>

<!-- Hasil tracking -->
<div id="tracking-result" style="display:none;">
    <h3>Hasil Tracking</h3>
    <p><strong>No. Resi:</strong> <span id="resi-no"></span></p>
    <p><strong>Tanggal Masuk:</strong> <span id="entry-date"></span></p>
    <p><strong>Pengirim:</strong> <span id="sender"></span></p>
    <p><strong>Penerima:</strong> <span id="receiver"></span></p>
    <p><strong>Status:</strong> <span id="status"></span></p>
    <div id="delivered-info" style="display:none;">
        <p><strong>Diterima oleh:</strong> <span id="receiver-name"></span></p>
        <p><strong>Tanggal Diterima:</strong> <span id="delivered-at"></span></p>
    </div>
    <h4>Timeline</h4>
    <ul id="timeline"></ul>
</div>

<div id="tracking-error" style="display:none; color:red;"></div>

<script>
const API_BASE = 'https://your-domain.com/api/tracking';

async function trackResi() {
    const resi = document.getElementById('resi-input').value.trim();
    if (!resi) return;

    const resultDiv = document.getElementById('tracking-result');
    const errorDiv = document.getElementById('tracking-error');
    resultDiv.style.display = 'none';
    errorDiv.style.display = 'none';

    try {
        const res = await fetch(`${API_BASE}/${encodeURIComponent(resi)}`);
        if (!res.ok) {
            const err = await res.json();
            errorDiv.textContent = err.error || 'Resi tidak ditemukan';
            errorDiv.style.display = 'block';
            return;
        }
        const data = await res.json();

        document.getElementById('resi-no').textContent = data.resi_no;
        document.getElementById('entry-date').textContent = data.entry_date;
        document.getElementById('sender').textContent = data.sender;
        document.getElementById('receiver').textContent = data.receiver;
        document.getElementById('status').textContent = data.shipment_status;

        if (data.receiver_name) {
            document.getElementById('receiver-name').textContent = data.receiver_name;
            document.getElementById('delivered-at').textContent = data.delivered_at;
            document.getElementById('delivered-info').style.display = 'block';
        } else {
            document.getElementById('delivered-info').style.display = 'none';
        }

        const timeline = document.getElementById('timeline');
        timeline.innerHTML = '';
        data.tracking_events.forEach(function(ev) {
            const li = document.createElement('li');
            li.textContent = ev.event_time + ' — ' + ev.event_type + (ev.location ? ' (' + ev.location + ')' : '') + (ev.notes ? ' — ' + ev.notes : '');
            timeline.appendChild(li);
        });

        resultDiv.style.display = 'block';
    } catch (e) {
        errorDiv.textContent = 'Gagal mengambil data. Coba lagi.';
        errorDiv.style.display = 'block';
    }
}
</script>
```

## Catatan
- Ganti `https://your-domain.com` dengan domain hosting Laravel lo
- CORS sudah diaktifkan, bisa dipanggil dari domain WordPress manapun
- Endpoint public (tanpa auth), zero PII (hanya info tracking yang dibatasi)
- Tracking events diurutkan ascending (terlama ke terbaru)