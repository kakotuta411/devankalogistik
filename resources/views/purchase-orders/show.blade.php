@extends('layouts.app')
@section('title', 'Detail PO')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Detail PO: {{ $po->po_number }}</h4>
        <div>
            @if(auth()->user()->canManagePurchaseOrders())
            <a href="{{ route('invoices.create', ['po_id' => $po->id]) }}" class="btn btn-sm btn-primary">Buat Invoice</a>
            @endif
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <p><strong>Customer:</strong> {{ $po->customer?->company_name ?? $po->customer?->name ?? '-' }}</p>
                    <p><strong>Status:</strong> <span class="badge {{ $po->getStatusBadgeClass() }}">{{ ucfirst($po->status) }}</span></p>
                    <p><strong>Total:</strong> {{ number_format($po->total_amount, 0, ',', '.') }}</p>
                    <p><strong>Dibayar:</strong> {{ number_format($po->paid_amount, 0, ',', '.') }}</p>
                    <p><strong>Sisa:</strong> {{ number_format($po->getRemainingAmount(), 0, ',', '.') }}</p>
                    @if($po->notes)
                    <p><strong>Catatan:</strong> {{ $po->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <h5>Resi terkait PO ini</h5>
    <div class="table-responsive mb-3">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Resi No</th>
                    <th>Tanggal</th>
                    <th>Pengirim</th>
                    <th>Penerima</th>
                    <th>Status</th>
                    <th>Berat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shipments as $s)
                <tr>
                    <td><a href="{{ route('shipments.show', $s) }}">{{ $s->resi_no }}</a></td>
                    <td>{{ $s->created_at?->format('d/m/Y') }}</td>
                    <td>{{ $s->sender_name }}</td>
                    <td>{{ $s->receiver_name }}</td>
                    <td>{{ $s->shipment_status }}</td>
                    <td>{{ $s->total_weight }} kg</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada resi terkait</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h5>Invoice terkait PO ini</h5>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Dibayar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td><a href="{{ route('invoices.show', $inv) }}">{{ $inv->invoice_no }}</a></td>
                    <td>{{ $inv->invoice_date?->format('d/m/Y') }}</td>
                    <td>{{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                    <td>{{ number_format($inv->amount_paid, 0, ',', '.') }}</td>
                    <td>{{ $inv->invoice_status }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada invoice</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection