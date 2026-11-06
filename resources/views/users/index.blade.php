@extends('layouts.app')
@section('page_header')<h1 class="h3 mb-0">Users</h1>@endsection
@section('toolbar')
    <div class="btn-group">
        @if(auth()->user()?->isSuperAdmin())
        <a href="/users/create" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah User</a>
        @endif
    </div>
@endsection
@section('content')
@php
    function sortLink($col, $label) {
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

<p class="text-body-secondary small mb-3">{{ $users->total() }} user(s)</p>

<form method="GET" class="d-flex flex-wrap gap-2 align-items-end mb-3">
    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" style="max-width:260px;" placeholder="Nama / email...">
    <select name="role" class="form-select form-select-sm" style="max-width:180px;">
        <option value="">Semua Role</option>
        @foreach(['Admin','Management_View','Staff_Invoice','Staff_Shipment'] as $role)
        <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>{{ $role }}</option>
        @endforeach
    </select>
    <select name="branch_id" class="form-select form-select-sm" style="max-width:190px;">
        <option value="">Semua Cabang</option>
        @foreach($branches ?? [] as $b)
        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->branch_code }} — {{ $b->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel"></i> Filter</button>
</form>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{!! sortLink('name', 'Nama') !!}</th>
                        <th>{!! sortLink('email', 'Email') !!}</th>
                        <th>{!! sortLink('role', 'Role') !!}</th>
                        <th>{!! sortLink('branch_id', 'Branch') !!}</th>
                        <th>{!! sortLink('created_at', 'Dibuat') !!}</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge text-bg-secondary">{{ $u->role }}</span></td>
                        <td>{{ $u->branch?->name ?? '-' }}</td>
                        <td><small class="text-body-secondary" data-uts="{{ $u->created_at?->getTimestamp() }}">{{ $u->created_at?->format('d/m/Y H:i') }}</small></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-warning edit-btn" data-edit-url="/users/{{ $u->id }}/edit" data-edit-title="Edit User"><i class="bi bi-pencil"></i></button>
                                <form action="/users/{{ $u->id }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus" onclick="return confirm('Hapus user?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-body-secondary">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $users->appends(request()->query())->links() }}</div>

<!-- Edit Modal (AJAX, not iframe) -->
<style>
#editModal .modal-dialog { width: 95vw; max-width: 95vw; height: 95vh; }
#editModal .modal-content { height: 100%; }
#editModal .modal-body { overflow-y: auto; }
</style>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editFormBody">
                <div class="text-center p-4">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModalEl = document.getElementById('editModal');
    var editModal = null;

    document.querySelectorAll('.edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = this.getAttribute('data-edit-url');
            var title = this.getAttribute('data-edit-title') || 'Edit';
            if (!editModal) editModal = new bootstrap.Modal(editModalEl);
            document.querySelector('#editModal .modal-title').textContent = title;
            document.getElementById('editFormBody').innerHTML = '<div class="text-center p-4">Loading...</div>';
            editModal.show();

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var form = doc.querySelector('form input[name="_method"][value="PUT"]') ? doc.querySelector('form input[name="_method"][value="PUT"]').closest('form') : doc.querySelector('form');
                    if (form) {
                        var cancel = form.querySelector('.btn-outline-secondary[href]');
                        if (cancel) cancel.remove();
                        document.getElementById('editFormBody').innerHTML = '';
                        document.getElementById('editFormBody').appendChild(form);
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            var fd = new FormData(form);
                            fetch(form.action, {
                                method: 'POST', body: fd,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            }).then(function(resp) {
                                if (resp.ok || resp.redirected) {
                                    editModal.hide();
                                } else {
                                    return resp.text();
                                }
                            }).then(function(html) {
                                if (html) {
                                    document.getElementById('editFormBody').innerHTML = '<div class="alert alert-danger">' + html.substring(0, 300) + '</div>';
                                }
                            }).catch(function() {
                                editModal.hide();
                            });
                        });
                    }
                });
        });
    });

    editModalEl.addEventListener('hidden.bs.modal', function() {
        document.getElementById('editFormBody').innerHTML = '';
        window.location.reload();
    });
});
</script>
@endsection