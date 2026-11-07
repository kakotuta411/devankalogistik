<?php $__env->startSection('title', 'Shipment Detail'); ?>
<?php $__env->startSection('page_header'); ?><h1 class="h2">Resi: <?php echo e($shipment->resi_no); ?></h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $s = $shipment; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="btn-group">
        <?php if(auth()->user()->canManageShipments()): ?>
        <button type="button" class="btn btn-outline-secondary btn-sm edit-btn" data-edit-url="/shipments/<?php echo e($s->id); ?>/edit" data-edit-title="Edit Shipment"><i class="bi bi-pencil"></i> Edit</button>
        <?php if(!in_array($s->shipment_status, ['Delivered', 'Cancelled', 'On Hold'])): ?>
        <form action="/shipments/<?php echo e($s->id); ?>/hold" method="POST" style="display:inline;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-outline-warning btn-sm" onclick="return confirm('Hold resi ini? Akan dikeluarkan dari manifest.')"><i class="bi bi-pause-circle"></i> Hold</button>
        </form>
        <form action="/shipments/<?php echo e($s->id); ?>/cancel" method="POST" style="display:inline;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Batalkan resi ini? Akan dikeluarkan dari manifest.')"><i class="bi bi-x-circle"></i> Cancel</button>
        </form>
        <?php endif; ?>
        <?php endif; ?>
        <a href="/print/shipments/<?php echo e($s->id); ?>/resi?v=<?php echo e(time()); ?>" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-printer"></i> Print Resi</a>
        <a href="/print/shipments/<?php echo e($s->id); ?>/label?v=<?php echo e(time()); ?>" target="_blank" class="btn btn-outline-success btn-sm"><i class="bi bi-printer"></i> Print Label</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><strong>Info Pengiriman</strong></div>
            <div class="card-body">
                <p class="mb-1"><strong>Status:</strong> <span class="badge <?php echo e($s->shipment_status === 'Delivered' ? 'text-bg-success' : ($s->shipment_status === 'Out for Delivery' ? 'text-bg-primary' : ($s->shipment_status === 'Arrived at Destination' ? 'text-bg-info' : ($s->shipment_status === 'In Transit' ? 'text-bg-warning' : 'text-bg-secondary')))); ?>"><?php echo e($s->shipment_status); ?></span></p>
                <p class="mb-1"><strong>Asal:</strong> <?php echo e($s->originBranch?->name); ?> (<?php echo e($s->originBranch?->city); ?>)</p>
                <p class="mb-1"><strong>Tujuan:</strong> <?php echo e($s->destinationBranch?->name); ?> (<?php echo e($s->destinationBranch?->city); ?>)</p>
                <p class="mb-1"><strong>Koli:</strong> <?php echo e($s->total_koli); ?></p>
                <p class="mb-0"><strong>Berat:</strong> <?php echo e($s->total_weight); ?> kg</p>
                <p class="mb-0"><strong>Layanan:</strong> <?php echo e($s->service_type); ?></p>
                <p class="mb-0"><strong>Detail Paket:</strong> <?php echo e($s->package_description ?? '-'); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><strong>Pengirim & Penerima</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Pengirim:</strong> <?php echo e($s->sender_name); ?>

                    <br><small class="text-muted"><?php echo e($s->sender_phone); ?> | <?php echo e($s->sender_address); ?></small>
                </div>
                <div>
                    <strong>Penerima:</strong> <?php echo e($s->receiver_name); ?>

                    <br><small class="text-muted"><?php echo e($s->receiver_phone); ?> | <?php echo e($s->receiver_address); ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($s->manifests->isNotEmpty()): ?>
<div class="card mb-4">
    <div class="card-header"><strong>Manifest Terkait</strong></div>
    <div class="card-body">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr><th>Manifest No</th><th>Dari</th><th>Ke</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $s->manifests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><a href="/manifests/<?php echo e($m->id); ?>"><?php echo e($m->manifest_no); ?></a></td>
                    <td><?php echo e($m->originBranch?->name); ?></td>
                    <td><?php echo e($m->destinationBranch?->name); ?></td>
                    <td><?php echo e($m->manifest_status); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><strong>Tracking Timeline</strong></div>
    <div class="card-body">
        <?php $__currentLoopData = $s->trackingEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="border-start border-primary ps-3 mb-3">
            <h6 class="mb-1"><?php echo e($ev->event_type); ?></h6>
            <p class="mb-1 text-muted small"><?php echo e($ev->location); ?> — <span data-uts="<?php echo e($ev->event_time?->getTimestamp()); ?>"><?php echo e($ev->event_time?->format('d/m/Y H:i')); ?></span></p>
            <?php if($ev->notes): ?><p class="mb-0 small"><?php echo e($ev->notes); ?></p><?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<?php if($s->pod_url): ?>
<div class="mt-4">
    <h5>POD Image</h5>
    <img src="<?php echo e(Storage::url($s->pod_url)); ?>" class="img-fluid border rounded" alt="POD">
</div>
<?php endif; ?>

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
function setupCascading(prefix) {
    var prov = document.getElementById(prefix + '_province');
    var reg = document.getElementById(prefix + '_regency');
    var dist = document.getElementById(prefix + '_district');
    var vill = document.getElementById(prefix + '_village');
    if (prov) prov.addEventListener('change', function() {
        loadOptions('/api/regions/regencies?province=' + this.value, prefix + '_regency');
        if (dist) dist.innerHTML = '<option value="">-- Pilih --</option>';
        if (vill) vill.innerHTML = '<option value="">-- Pilih --</option>';
    });
    if (reg) reg.addEventListener('change', function() {
        loadOptions('/api/regions/districts?regency=' + this.value, prefix + '_district');
        if (vill) vill.innerHTML = '<option value="">-- Pilih --</option>';
    });
    if (dist) dist.addEventListener('change', function() {
        loadOptions('/api/regions/villages?district=' + this.value, prefix + '_village');
    });
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
                        function initCascading(prefix) {
                            var provSel = document.getElementById(prefix + '_province');
                            var regSel = document.getElementById(prefix + '_regency');
                            var distSel = document.getElementById(prefix + '_district');
                            var villSel = document.getElementById(prefix + '_village');
                            if (!provSel) return;

                            var selProv = provSel.getAttribute('data-selected') || '';
                            var selReg = regSel ? (regSel.getAttribute('data-selected') || '') : '';
                            var selDist = distSel ? (distSel.getAttribute('data-selected') || '') : '';
                            var selVill = villSel ? (villSel.getAttribute('data-selected') || '') : '';

                            // Load provinces
                            loadOptions('/api/regions/provinces', prefix + '_province');

                            if (selProv) {
                                setTimeout(function() {
                                    provSel.value = selProv;
                                    // Load regencies
                                    loadOptions('/api/regions/regencies?province=' + selProv, prefix + '_regency');
                                    if (selReg) {
                                        setTimeout(function() {
                                            regSel.value = selReg;
                                            // Load districts
                                            loadOptions('/api/regions/districts?regency=' + selReg, prefix + '_district');
                                            if (selDist) {
                                                setTimeout(function() {
                                                    distSel.value = selDist;
                                                    // Load villages
                                                    loadOptions('/api/regions/villages?district=' + selDist, prefix + '_village');
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

                            setupCascading(prefix);
                        }

                        initCascading('sender');
                        initCascading('receiver');

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/shipments/show.blade.php ENDPATH**/ ?>