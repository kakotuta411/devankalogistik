@extends('layouts.app')
@section('title', 'Edit User')
@section('page_header')<h1 class="h2">Edit User: {{ $user->name }}</h1>@endsection
@section('content')
<form action="/users/{{ $user->id }}" method="POST" class="card">
    @csrf @method('PUT')
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama *</label>
                <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Password (kosongkan jika tidak diubah)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label">Role *</label>
                <select name="role" class="form-select" required>
                    <option value="Admin" {{ $user->role === 'Admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="Management_View" {{ $user->role === 'Management_View' ? 'selected' : '' }}>Management View (read-only)</option>
                    <option value="Staff_Invoice" {{ $user->role === 'Staff_Invoice' ? 'selected' : '' }}>Staff Invoice</option>
                    <option value="Staff_Shipment" {{ $user->role === 'Staff_Shipment' ? 'selected' : '' }}>Staff Shipment + Manifest</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">-- Tidak ada --</option>
                    @foreach(\App\Models\Branch::all() as $b)
                        <option value="{{ $b->id }}" {{ $user->branch_id === $b->id ? 'selected' : '' }}>{{ $b->branch_code }} — {{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Telepon</label>
            <input type="text" name="phone_number" value="{{ $user->phone_number }}" class="form-control" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="/users" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
@endsection
