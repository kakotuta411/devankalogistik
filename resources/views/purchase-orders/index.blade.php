@extends('layouts.app')
@section('title', 'Purchase Orders')
@section('page_header')<h1 class="h2">Purchase Orders</h1>@endsection
@section('content')
<div class="mb-3">
    <a href="/purchase-orders/create" class="btn btn-primary">Buat PO Baru</a>
</div>
<table class="table table-hover align-middle">
    <thead>
        <tr>
            <th>No. PO</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Dibayar</th>
            <th>Sisa</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    @foreach($pos as $po)
        <tr>
            <td><a href="/purchase-orders/{{ $po->id }}">{{ $po->po_number }}</a></td>
            <td>{{ $po->customer?->company_name ?? $po->customer?->name ?? '-' }}</td>
            <td>Rp {{ number_format($po->total_amount ?? 0, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($po->paid_amount ?? 0, 0, ',', '.') }}</td>
            <td>Rp {{ number_format(($po->total_amount ?? 0) - ($po->paid_amount ?? 0), 0, ',', '.') }}</td>
            <td>{{ $po->status }}</td>
            <td>
                <a href="/purchase-orders/{{ $po->id }}/edit" class="btn btn-sm btn-outline-primary">Edit</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $pos->links() }}
@endsection
