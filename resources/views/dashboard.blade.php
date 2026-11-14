@extends('layouts.app')
@section('page_header')
    <h1 class="h2">Dashboard</h1>
@endsection
@section('toolbar')
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="input-group input-group-sm" style="max-width:280px;">
            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
            <input type="date" class="form-control form-control-sm" id="startDate"
                value="{{ $stats['date_range']['start_date'] }}">
            <span class="input-group-text">to</span>
            <input type="date" class="form-control form-control-sm" id="endDate"
                value="{{ $stats['date_range']['end_date'] }}">
        </div>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary" id="presetBtn" data-bs-toggle="dropdown">
                <i class="bi bi-funnel"></i> Presets
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item preset-opt" href="#" data-days="7">Last 7 Days</a></li>
                <li><a class="dropdown-item preset-opt" href="#" data-days="30">Last 30 Days</a></li>
                <li><a class="dropdown-item preset-opt active" href="#" data-days="this_month">This Month</a></li>
                <li><a class="dropdown-item preset-opt" href="#" data-days="0">All Time (max 90d)</a></li>
            </ul>
            <button type="button" class="btn btn-outline-secondary" id="applyFilter">
                <i class="bi bi-check2"></i> Apply
            </button>
        </div>
    </div>
@endsection
@section('content')
    @php
        function sortLink($col, $label)
        {
            $currentSort = request('sort', '');
            $currentDir = request('dir', 'asc');
            $dir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
            $icon = '';
            if ($currentSort === $col) {
                $icon = $currentDir === 'asc' ? ' ↑' : ' ↓';
            }
            $qs = http_build_query(array_merge(request()->query(), ['sort' => $col, 'dir' => $dir]));
            return '<a href="?' . $qs . '" class="text-decoration-none text-dark d-flex align-items-center gap-1">' . $label . $icon . '</a>';
        }
    @endphp
    <div class="row g-3 mb-3">
        <div class="col ">
            <div class="card text-bg-success mb-3" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalShipments">
                <div class="card-header">Shipments</div>
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $stats['shipments_today'] }}</h5>
                    <p class="card-text small">Total shipments</p>
                </div>
            </div>
        </div>
        <div class="col ">
            <div class="card text-bg-success mb-3" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalManifests">
                <div class="card-header">Manifests</div>
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $stats['manifests_today'] }}</h5>
                    <p class="card-text small">Total manifests</p>
                </div>
            </div>
        </div>
        <div class="col ">
            <div class="card text-bg-success mb-3" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalInvoices">
                <div class="card-header">Invoices</div>
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $stats['invoices_today'] }}</h5>
                    <p class="card-text small">Total invoices</p>
                </div>
            </div>
        </div>
        <div class="col ">
            <div class="card text-bg-success mb-3">
                <div class="card-header">Invoice Lunas</div>
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $stats['invoices_paid'] }}</h5>
                    <p class="card-text small">{{ number_format($stats['invoices_paid_amount'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col ">
            <div class="card text-bg-warning mb-3">
                <div class="card-header">Invoice Pending</div>
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $stats['invoices_pending'] }}</h5>
                    <p class="card-text small">{{ number_format($stats['invoices_pending_amount'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <!-- <div class="col ">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h5 class="card-title">{{ $stats['branches'] }}</h5>
                                                            <p class="card-text text-body-secondary small">Total Branches</p>
                                                        </div>
                                                    </div>
                                                </div> -->
    </div>

    <div class="row g-3">
        <!-- Invoice Pending -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><strong>Invoice Pending</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th style="width:42%">Progress Pembayaran</th>
                                    <th class="text-end">Sisa Tunggakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['pending_invoices'] as $inv)
                                <tr>
                                    <td>
                                        <a href="/invoices/{{ $inv->id }}" class="text-decoration-none fw-medium">{{ $inv->customer_name ?? '-' }}</a>
                                        <br><small class="text-body-secondary">{{ $inv->invoice_no }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $barClass = $inv->progress_pct >= 70 ? 'bg-success' : ($inv->progress_pct >= 30 ? 'bg-warning' : 'bg-danger');
                                        @endphp
                                        <div class="progress" style="height:18px;">
                                            <div class="progress-bar {{ $barClass }}" role="progressbar"
                                                style="width: {{ $inv->progress_pct }}%;"
                                                aria-valuenow="{{ $inv->progress_pct }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $inv->progress_pct }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($inv->sisa_tunggakan, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-body-secondary py-3">Tidak ada invoice pending</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer"><a href="/invoices" class="btn btn-sm btn-outline-primary">Lihat Semua</a></div>
            </div>
        </div>

        <!-- PO Pending -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><strong>PO Pending</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th style="width:42%">Progress Pembayaran</th>
                                    <th class="text-end">Sisa Tunggakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['pending_pos'] as $po)
                                <tr>
                                    <td>
                                        <a href="/purchase-orders/{{ $po->id }}" class="text-decoration-none fw-medium">{{ $po->customer?->company_name ?? $po->customer?->name ?? '-' }}</a>
                                        <br><small class="text-body-secondary">{{ $po->po_number }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $barClass = $po->progress_pct >= 70 ? 'bg-success' : ($po->progress_pct >= 30 ? 'bg-warning' : 'bg-danger');
                                        @endphp
                                        <div class="progress" style="height:18px;">
                                            <div class="progress-bar {{ $barClass }}" role="progressbar"
                                                style="width: {{ $po->progress_pct }}%;"
                                                aria-valuenow="{{ $po->progress_pct }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $po->progress_pct }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($po->sisa_tunggakan, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-body-secondary py-3">Tidak ada PO pending</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer"><a href="/purchase-orders" class="btn btn-sm btn-outline-primary">Lihat Semua</a></div>
            </div>
        </div>
    </div>

    <!-- Modal: Shipments -->
    <div class="modal fade" id="modalShipments" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Shipments ({{ $stats['shipments_today'] }})</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Resi</th>
                                    <th>Asal</th>
                                    <th>Tujuan</th>
                                    <th>Status</th>
                                    <th>Layanan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_shipments'] as $s)
                                    @php
                                        $sc = ['Created'=>'secondary','In Transit'=>'info','Arrived at Destination'=>'primary','Delivered'=>'success','On Hold'=>'warning','Cancelled'=>'danger'];
                                    @endphp
                                    <tr>
                                        <td><a href="/shipments/{{ $s->id }}">{{ $s->resi_no }}</a></td>
                                        <td>{{ $s->originBranch?->city ?? '-' }}</td>
                                        <td>{{ $s->destinationBranch?->city ?? '-' }}</td>
                                        <td><span class="badge text-bg-{{ $sc[$s->shipment_status] ?? 'secondary' }}">{{ $s->shipment_status }}</span></td>
                                        <td>{{ $s->service_type }}</td>
                                    </tr>
                                @endforeach
                                @if($stats['recent_shipments']->isEmpty())
                                    <tr><td colspan="5" class="text-center text-body-secondary py-3">No shipments</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/shipments" class="btn btn-sm btn-primary">Lihat Semua</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Manifests -->
    <div class="modal fade" id="modalManifests" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manifests ({{ $stats['manifests_today'] }})</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No Manifest</th>
                                    <th>Asal</th>
                                    <th>Tujuan</th>
                                    <th>Status</th>
                                    <th>Kendaraan</th>
                                    <th>Driver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_manifests'] as $m)
                                    @php
                                        $mc = ['Draft'=>'secondary','Dispatched'=>'warning','Received'=>'info','Completed'=>'success'];
                                    @endphp
                                    <tr>
                                        <td><a href="/manifests/{{ $m->id }}">{{ $m->manifest_no }}</a></td>
                                        <td>{{ $m->originBranch?->city ?? '-' }}</td>
                                        <td>{{ $m->destinationBranch?->city ?? '-' }}</td>
                                        <td><span class="badge text-bg-{{ $mc[$m->manifest_status] ?? 'secondary' }}">{{ $m->manifest_status }}</span></td>
                                        <td>{{ $m->vehicle_no ?? '-' }}</td>
                                        <td>{{ $m->driver_name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                                @if($stats['recent_manifests']->isEmpty())
                                    <tr><td colspan="6" class="text-center text-body-secondary py-3">No manifests</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/manifests" class="btn btn-sm btn-primary">Lihat Semua</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Invoices -->
    <div class="modal fade" id="modalInvoices" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invoices ({{ $stats['invoices_today'] }})</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No Invoice</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Subtotal</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_invoices'] as $inv)
                                    @php
                                        $ic = ['Draft'=>'secondary','Paid'=>'success','Cancelled'=>'danger'];
                                    @endphp
                                    <tr>
                                        <td><a href="/invoices/{{ $inv->id }}">{{ $inv->invoice_no }}</a></td>
                                        <td>{{ $inv->customer_name }}</td>
                                        <td><span class="badge text-bg-{{ $ic[$inv->invoice_status] ?? 'secondary' }}">{{ $inv->invoice_status }}</span></td>
                                        <td>Rp {{ number_format($inv->subtotal, 0, ',', '.') }}</td>
                                        <td>{{ $inv->created_at?->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                                @if($stats['recent_invoices']->isEmpty())
                                    <tr><td colspan="5" class="text-center text-body-secondary py-3">No invoices</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/invoices" class="btn btn-sm btn-primary">Lihat Semua</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var startDate = document.getElementById('startDate');
            var endDate = document.getElementById('endDate');
            var applyBtn = document.getElementById('applyFilter');
            // Limit endDate to 90 days from startDate
            startDate.addEventListener('change', function () {
                var start = new Date(startDate.value);
                var maxEnd = new Date(start);
                maxEnd.setDate(start.getDate() + 90);
                endDate.min = startDate.value;
                endDate.max = maxEnd.toISOString().split('T')[0];
            });

            // Apply filter
            applyBtn.addEventListener('click', function () {
                var qs = new URLSearchParams(window.location.search);
                qs.set('start_date', startDate.value);
                qs.set('end_date', endDate.value);
                qs.delete('sort');
                qs.delete('dir');
                window.location.search = qs.toString();
            });

            // Preset options
            document.querySelectorAll('.preset-opt').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    var days = el.getAttribute('data-days');
                    var now = new Date();
                    if (days === 'this_month') {
                        var first = new Date(now.getFullYear(), now.getMonth(), 1);
                        startDate.value = first.toISOString().split('T')[0];
                    } else if (days === '0') {
                        var d = new Date(now);
                        d.setDate(now.getDate() - 90);
                        startDate.value = d.toISOString().split('T')[0];
                    } else {
                        var d = new Date(now);
                        d.setDate(now.getDate() - parseInt(days));
                        startDate.value = d.toISOString().split('T')[0];
                    }
                    endDate.value = now.toISOString().split('T')[0];
                    // Trigger apply
                    applyBtn.click();
                });
            });

            // Enter key on date inputs = apply
            [startDate, endDate].forEach(function (inp) {
                inp.addEventListener('keypress', function (e) {
                    if (e.which === 13) { applyBtn.click(); }
                });
            });

        });
    </script>
@endpush