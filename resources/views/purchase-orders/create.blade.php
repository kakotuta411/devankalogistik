@extends('layouts.app')
@section('title', 'Buat PO')
@section('content')
<div class="container-fluid">
    <h4>Buat Purchase Order</h4>
    <form method="POST" action="{{ route('purchase-orders.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nomor PO *</label>
                        <input type="text" name="po_number" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer *</label>
                        <select name="customer_id" class="form-control" required>
                            <option value="">Pilih Customer</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name ?? $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection