@extends('layouts.app')
@section('title', 'Branch Detail')
@section('page_header')<h1 class="h2">{{ $branch->name }}</h1>@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <p class="mb-1"><strong>Kode:</strong> {{ $branch->branch_code }}</p>
        <p class="mb-1"><strong>Kota:</strong> {{ $branch->city }}</p>
        <p class="mb-0"><strong>Alamat:</strong> {{ $branch->address }}</p>
    </div>
</div>
@endsection
