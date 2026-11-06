@extends('layouts.app')
@section('title', 'Edit Manifest')
@section('page_header')<h1 class="h2">Edit Manifest</h1>@endsection
@section('content')
<form action="/manifests/{{ $manifest->id }}" method="POST" class="card">
    @csrf @method('PUT')
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6">
                <label for="vehicle_no" class="form-label">No Kendaraan</label>
                <input type="text" name="vehicle_no" value="{{ $manifest->vehicle_no }}" class="form-control" id="vehicle_no">
            </div>
            <div class="col-sm-6">
                <label for="driver_name" class="form-label">Nama Driver</label>
                <input type="text" name="driver_name" value="{{ $manifest->driver_name }}" class="form-control" id="driver_name">
            </div>
            <div class="col-sm-6">
            </div>
            <div class="col-sm-6">
                <label for="destination_branch_id" class="form-label">Cabang Tujuan</label>
                <select name="destination_branch_id" class="form-control" id="destination_branch_id">
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $b->id == $manifest->destination_branch_id ? 'selected' : '' }}>{{ $b->branch_code }} — {{ $b->name }} ({{ $b->city }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="/manifests" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
@endsection
