<?php $__env->startSection('page_header'); ?><h1 class="h3 mb-0">Users</h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('toolbar'); ?>
    <div class="btn-group">
        <?php if(auth()->user()?->isSuperAdmin()): ?>
        <a href="/users/create" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah User</a>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php
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
?>

<p class="text-body-secondary small mb-3"><?php echo e($users->total()); ?> user(s)</p>

<form method="GET" class="d-flex flex-wrap gap-2 align-items-end mb-3">
    <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control form-control-sm" style="max-width:260px;" placeholder="Nama / email...">
    <select name="role" class="form-select form-select-sm" style="max-width:180px;">
        <option value="">Semua Role</option>
        <?php $__currentLoopData = ['Admin','Head_of_Branch','Leader_Supervisor','Executive_Viewer','Staf']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($role); ?>" <?php echo e(request('role') == $role ? 'selected' : ''); ?>><?php echo e($role); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="branch_id" class="form-select form-select-sm" style="max-width:190px;">
        <option value="">Semua Cabang</option>
        <?php $__currentLoopData = $branches ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($b->id); ?>" <?php echo e(request('branch_id') == $b->id ? 'selected' : ''); ?>><?php echo e($b->branch_code); ?> — <?php echo e($b->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel"></i> Filter</button>
</form>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?php echo sortLink('name', 'Nama'); ?></th>
                        <th><?php echo sortLink('email', 'Email'); ?></th>
                        <th><?php echo sortLink('role', 'Role'); ?></th>
                        <th><?php echo sortLink('branch_id', 'Branch'); ?></th>
                        <th><?php echo sortLink('created_at', 'Dibuat'); ?></th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($u->name); ?></td>
                        <td><?php echo e($u->email); ?></td>
                        <td><span class="badge text-bg-secondary"><?php echo e($u->role); ?></span></td>
                        <td><?php echo e($u->branch?->name ?? '-'); ?></td>
                        <td><small class="text-body-secondary" data-uts="<?php echo e($u->created_at?->getTimestamp()); ?>"><?php echo e($u->created_at?->format('d/m/Y H:i')); ?></small></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-warning edit-btn" data-edit-url="/users/<?php echo e($u->id); ?>/edit" data-edit-title="Edit User"><i class="bi bi-pencil"></i></button>
                                <form action="/users/<?php echo e($u->id); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus" onclick="return confirm('Hapus user?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="text-center text-body-secondary">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3"><?php echo e($users->appends(request()->query())->links()); ?></div>

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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/users/index.blade.php ENDPATH**/ ?>