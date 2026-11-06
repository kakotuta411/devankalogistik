<?php $__env->startSection('title', 'Manifest Detail'); ?>
<?php $__env->startSection('page_header'); ?><h1 class="h2">Manifest: <?php echo e($manifest->manifest_no); ?></h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $m = $manifest; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="/print/manifests/<?php echo e($m->id); ?>/sheet" target="_blank" class="btn btn-success"><i class="bi bi-printer me-2"></i>Print Sheet</a>
    <?php if($m->manifest_status === 'Draft'): ?>
    <?php if(auth()->user()->role !== "Executive_Viewer"): ?>
        <button type="button" class="btn btn-outline-warning edit-btn" data-edit-url="/manifests/<?php echo e($m->id); ?>/edit" data-edit-title="Edit Manifest"><i class="bi bi-pencil me-2"></i>Edit</button>
        <?php endif; ?>
    <?php endif; ?>
</div>
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><strong>Info Manifest</strong></div>
            <div class="card-body">
                <p class="mb-1"><strong>Status:</strong> <span class="badge <?php echo e($m->manifest_status === 'Completed' || $m->manifest_status === 'Cancelled' ? 'text-bg-success' : ($m->manifest_status === 'Received' ? 'text-bg-info' : ($m->manifest_status === 'Dispatched' ? 'text-bg-warning' : 'text-bg-secondary'))); ?>"><?php echo e($m->manifest_status); ?></span></p>
                <p class="mb-1"><strong>Asal:</strong> <?php echo e($m->originBranch?->name); ?> (<?php echo e($m->originBranch?->city); ?>)</p>
                <p class="mb-1"><strong>Tujuan:</strong> <?php echo e($m->destinationBranch?->name); ?> (<?php echo e($m->destinationBranch?->city); ?>)</p>
                <p class="mb-1"><strong>Kendaraan:</strong> <?php echo e($m->vehicle_no ?? '-'); ?></p>
                <p class="mb-1"><strong>Driver:</strong> <?php echo e($m->driver_name ?? '-'); ?></p>
                <p class="mb-1"><strong>Dispatch:</strong> <span data-uts="<?php echo e($m->dispatch_time?->getTimestamp()); ?>" class="text-body-secondary"><?php echo e($m->dispatch_time?->format('d/m/Y H:i') ?? '-'); ?></span></p>
                <p class="mb-0"><strong>Received:</strong> <span data-uts="<?php echo e($m->receive_time?->getTimestamp()); ?>" class="text-body-secondary"><?php echo e($m->receive_time?->format('d/m/Y H:i') ?? '-'); ?></span></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><strong>Daftar Muatan</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>No</th><th>Resi</th><th>Tujuan</th><th>Koli</th></tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $m->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($i + 1); ?></td>
                            <td><a href="/shipments/<?php echo e($item->shipment?->id); ?>" class="text-decoration-none"><?php echo e($item->shipment?->resi_no); ?></a></td>
                            <td><?php echo e($item->shipment?->destinationBranch?->city); ?></td>
                            <td><?php echo e($item->shipment?->total_koli); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="d-flex gap-2">
    <?php if(auth()->user()->role !== "Executive_Viewer"): ?>
    <?php if($m->manifest_status === 'Draft'): ?>
        <form action="/manifests/<?php echo e($m->id); ?>/dispatch" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-warning">Dispatch</button>
        </form>
    <?php endif; ?>
    <?php if($m->manifest_status === 'Dispatched'): ?>
        <form action="/manifests/<?php echo e($m->id); ?>/receive" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-success">Terima & Bongkar</button>
        </form>
    <?php endif; ?>
    <?php if($m->manifest_status === 'Received'): ?>
        <form action="/manifests/<?php echo e($m->id); ?>/complete" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-success">Selesaikan</button>
        </form>
    <?php endif; ?>
    <?php endif; ?>
<!-- Edit Modal -->
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/manifests/show.blade.php ENDPATH**/ ?>