@extends('layouts.app')
@section('title', 'Detail PO')
@section('page_header')<h1 class="h2">Detail PO: {{ $po->po_number }}</h1>@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <p><strong>Customer:</strong> {{ $po->customer?->company_name ?? $po->customer?->name ?? '-' }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($po->total_amount ?? 0, 0, ',', '.') }}</p>
        <p><strong>Dibayar:</strong> Rp {{ number_format($po->paid_amount ?? 0, 0, ',', '.') }}</p>
        <p><strong>Status:</strong> {{ $po->status }}</p>
        <p><strong>Catatan:</strong> {{ $po->notes }}</p>
    </div>
</div>
<h5>Resi Terkait</h5>
<table class="table table-sm">
    <thead><tr><th>Resi No</th><th>Pengirim</th><th>Penerima</th><th>Status</th></tr></thead>
    <tbody>
    @foreach($shipments as $s)
        <tr>
            <td><a href="/shipments/{{ $s->id }}">{{ $s->resi_no }}</a></td>
            <td>{{ $s->sender_name }}</td>
            <td>{{ $s->receiver_name }}</td>
            <td>{{ $s->shipment_status }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<h5>Invoice Terkait</h5>
<table class="table table-sm">
    <thead><tr><th>No. Invoice</th><th>Status</th><th>Total</th><th>Dibayar</th></tr></thead>
    <tbody>
    @foreach($invoices as $inv)
        <tr>
            <td><a href="/invoices/{{ $inv->id }}">{{ $inv->invoice_no }}</a></td>
            <td>{{ $inv->invoice_status }}</td>
            <td>Rp {{ number_format($inv->total_amount ?? 0, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($inv->amount_paid ?? 0, 0, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="mt-3">
    <a href="/purchase-orders/{{ $po->id }}/edit" class="btn btn-outline-primary">Edit</a>
    <a href="/purchase-orders" class="btn btn-secondary">Kembali</a>
</div>
@endsection
