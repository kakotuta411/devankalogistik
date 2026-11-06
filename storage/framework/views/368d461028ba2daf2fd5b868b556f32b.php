<?php $__env->startSection('page_header'); ?><h1 class="h3 mb-0">Branches</h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('toolbar'); ?>
    <div class="btn-group">
        <?php if(auth()->user()?->isSuperAdmin()): ?>
        <a href="/branches/create" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah Cabang</a>
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

<p class="text-body-secondary small mb-3"><?php echo e($branches->total()); ?> branch(es)</p>

<form method="GET" class="d-flex flex-wrap gap-2 align-items-end mb-3">
    <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control form-control-sm" style="max-width:260px;" placeholder="Kode / nama cabang...">
    <select name="province_code" class="form-select form-select-sm" style="max-width:240px;">
        <option value="">Semua Provinsi</option>
        <?php $__currentLoopData = ($regions ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($r->code); ?>" <?php echo e(request('province_code') == $r->code ? 'selected' : ''); ?>><?php echo e($r->name); ?></option>
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
                        <th><?php echo sortLink('branch_code', 'Kode'); ?></th>
                        <th><?php echo sortLink('name', 'Nama'); ?></th>
                        <th><?php echo sortLink('city', 'Kota'); ?></th>
                        <th><?php echo sortLink('created_at', 'Dibuat'); ?></th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><span class="fw-medium"><?php echo e($b->branch_code); ?></span></td>
                        <td><?php echo e($b->name); ?></td>
                        <td><?php echo e($b->city); ?></td>
                        <td><small class="text-body-secondary" data-uts="<?php echo e($b->created_at?->getTimestamp()); ?>"><?php echo e($b->created_at?->format('d/m/Y H:i')); ?></small></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-warning edit-btn" data-edit-url="/branches/<?php echo e($b->id); ?>/edit" data-edit-title="Edit Branch"><i class="bi bi-pencil"></i></button>
                                <form action="/branches/<?php echo e($b->id); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus" onclick="return confirm('Hapus cabang?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-body-secondary">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3"><?php echo e($branches->appends(request()->query())->links()); ?></div>

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
function loadOptions(url, selectId) {
    fetch(url).then(r => r.json()).then(data => {
        var sel = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '<option value="">-- Pilih --</option>';
        data.forEach(function(item) {
            var opt = document.createElement('option');
            opt.value = item.code;
            opt.textContent = item.name;
            sel.appendChild(opt);
        });
    }).catch(function() {});
}
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

                        // Initialize cascading selects with pre-filled values
                        (function() {
                            var provSel = document.getElementById('province_code');
                            var regSel = document.getElementById('regency_code');
                            var distSel = document.getElementById('district_code');
                            var villSel = document.getElementById('village_code');
                            if (!provSel) return;

                            var selProv = provSel.getAttribute('data-selected') || '';
                            var selReg = regSel ? (regSel.getAttribute('data-selected') || '') : '';
                            var selDist = distSel ? (distSel.getAttribute('data-selected') || '') : '';
                            var selVill = villSel ? (villSel.getAttribute('data-selected') || '') : '';

                            loadOptions('/api/regions/provinces', 'province_code');

                            if (selProv) {
                                setTimeout(function() {
                                    provSel.value = selProv;
                                    loadOptions('/api/regions/regencies?province=' + selProv, 'regency_code');
                                    if (selReg) {
                                        setTimeout(function() {
                                            regSel.value = selReg;
                                            loadOptions('/api/regions/districts?regency=' + selReg, 'district_code');
                                            if (selDist) {
                                                setTimeout(function() {
                                                    distSel.value = selDist;
                                                    loadOptions('/api/regions/villages?district=' + selDist, 'village_code');
                                                    if (selVill) {
                                                        setTimeout(function() {
                                                            villSel.value = selVill;
                                                        }, 500);
                                                    }
                                                }, 500);
                                            }
                                        }, 500);
                                    }
                                }, 500);
                            }

                            provSel.addEventListener('change', function() {
                                loadOptions('/api/regions/regencies?province=' + this.value, 'regency_code');
                                if (distSel) distSel.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                                if (villSel) villSel.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
                            });
                            if (regSel) regSel.addEventListener('change', function() {
                                loadOptions('/api/regions/districts?regency=' + this.value, 'district_code');
                                if (villSel) villSel.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
                            });
                            if (distSel) distSel.addEventListener('change', function() {
                                loadOptions('/api/regions/villages?district=' + this.value, 'village_code');
                            });
                        })();
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/branches/index.blade.php ENDPATH**/ ?>