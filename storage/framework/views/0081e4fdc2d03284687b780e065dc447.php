<?php $__env->startSection('title', 'Invoice Detail'); ?>
<?php $__env->startSection('page_header'); ?><h1 class="h2">Invoice: <?php echo e($invoice->invoice_no); ?></h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $inv = $invoice; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="btn-group">
        <?php if(auth()->user()->canManageInvoices() && $inv->childInvoices->count() === 0): ?>
        <button type="button" class="btn btn-outline-secondary edit-btn" data-edit-url="/invoices/<?php echo e($inv->id); ?>/edit" data-edit-title="Edit Invoice"><i class="bi bi-pencil"></i> Edit</button>
        <?php endif; ?>
        <a href="/print/invoices/<?php echo e($inv->id); ?>/pdf" target="_blank" class="btn btn-success"><i class="bi bi-printer me-2"></i>Print</a>
    </div>
</div>
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><strong>Info Invoice</strong></div>
            <div class="card-body">
                <p class="mb-1"><strong>Status:</strong> <span class="badge <?php echo e($inv->invoice_status === 'Paid' ? 'text-bg-success' : ($inv->invoice_status === 'Cancelled' ? 'text-bg-danger' : ($inv->invoice_status === 'Partial' ? 'text-bg-warning' : 'text-bg-primary'))); ?>"><?php echo e($inv->invoice_status); ?></span></p>
                <p class="mb-1"><strong>Customer:</strong> <?php echo e($inv->customer_name ?? '-'); ?></p>
                <p class="mb-1"><strong>Telepon:</strong> <?php echo e($inv->customer_phone ?? '-'); ?></p>
                <p class="mb-1"><strong>Alamat:</strong> <?php echo e($inv->customer_address ?? '-'); ?></p>
                <p class="mb-1"><strong>Email:</strong> <?php echo e($inv->customer_email ?? '-'); ?></p>
                <p class="mb-1"><strong>Cabang:</strong> <?php echo e($inv->branch?->name ?? '-'); ?></p>
                <p class="mb-1"><strong>Tanggal:</strong> <?php echo e($inv->invoice_date?->format('d/m/Y') ?? '-'); ?></p>
                <p class="mb-0"><strong>Due Date:</strong> <?php echo e($inv->due_date?->format('d/m/Y') ?? '-'); ?></p>
                <?php if($inv->childInvoices->count() > 0): ?>
                <hr class="my-2">
                <p class="mb-1"><strong>Invoice Pecahan (Sisa):</strong></p>
                <?php $__currentLoopData = $inv->childInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p class="mb-1"><a href="/invoices/<?php echo e($child->id); ?>" class="text-decoration-none"><?php echo e($child->invoice_no); ?></a> — Rp <?php echo e(number_format($child->total_amount, 0, ',', '.')); ?> (<?php echo e($child->invoice_status); ?>)</p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
                <?php if($inv->parentInvoice): ?>
                <hr class="my-2">
                <p class="mb-1"><strong>Invoice Induk:</strong> <a href="/invoices/<?php echo e($inv->parentInvoice->id); ?>" class="text-decoration-none"><?php echo e($inv->parentInvoice->invoice_no); ?></a> — Rp <?php echo e(number_format($inv->parentInvoice->total_amount, 0, ',', '.')); ?> (<?php echo e($inv->parentInvoice->invoice_status); ?>)</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><strong>Ringkasan Finansial</strong></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <?php if($inv->isChild()): ?>
                    
                    <tr><td>Sisa dari invoice <a href="/invoices/<?php echo e($inv->parentInvoice->id); ?>" class="text-decoration-none"><?php echo e($inv->parentInvoice->invoice_no); ?></a></td><td class="text-end"></td></tr>
                    <tr class="table-active"><td><strong>TOTAL</strong></td><td class="text-end"><strong>Rp <?php echo e(number_format($inv->displayTotal(), 0, ',', '.')); ?></strong></td></tr>
                    <?php else: ?>
                    <tr><td>Biaya Pengiriman (<?php echo e(number_format($inv->shipping_cost, 0, ',', '.')); ?>/kg x <?php echo e(number_format($inv->total_weight, 2, ',', '.')); ?> kg)</td><td class="text-end">Rp <?php echo e(number_format($inv->shipping_cost * $inv->total_weight, 0, ',', '.')); ?></td></tr>
                    <tr><td>Biaya Packaging</td><td class="text-end">Rp <?php echo e(number_format($inv->packaging_cost, 0, ',', '.')); ?></td></tr>
                    <tr><td>Charge Alat</td><td class="text-end">Rp <?php echo e(number_format($inv->equipment_charge, 0, ',', '.')); ?></td></tr>
                    <tr><td>Biaya Lain-lain</td><td class="text-end">Rp <?php echo e(number_format($inv->other_costs, 0, ',', '.')); ?></td></tr>
                    <tr><td>Diskon (<?php echo e(number_format($inv->discount_value, 2, ',', '.')); ?>%)</td><td class="text-end">-Rp <?php echo e(number_format($inv->discount, 0, ',', '.')); ?></td></tr>
                    <tr><td>PPFTZ 03</td><td class="text-end">Rp <?php echo e(number_format($inv->ppftz03, 0, ',', '.')); ?></td></tr>
                    <tr><td>PPN (<?php echo e(number_format($inv->ppn, 2, ',', '.')); ?>%)</td><td class="text-end">Rp <?php echo e(number_format($inv->ppnAmount(), 0, ',', '.')); ?></td></tr>
                    <tr><td>PPH (<?php echo e(number_format($inv->pph, 2, ',', '.')); ?>%)</td><td class="text-end">Rp <?php echo e(number_format($inv->pphAmount(), 0, ',', '.')); ?></td></tr>
                    <tr class="table-active"><td><strong>TOTAL</strong></td><td class="text-end"><strong>Rp <?php echo e(number_format($inv->displayTotal(), 0, ',', '.')); ?></strong></td></tr>
                    <?php endif; ?>
                    <tr><td>Dibayar</td><td class="text-end">Rp <?php echo e(number_format($inv->amount_paid ?? 0, 0, ',', '.')); ?></td></tr>
                    <tr class="table-warning"><td><strong>Tunggakan</strong></td><td class="text-end"><strong>Rp <?php echo e(number_format($inv->displayTotal() - ($inv->amount_paid ?? 0), 0, ',', '.')); ?></strong></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header"><strong>Daftar Shipment</strong></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Resi</th><th>Penerima</th><th>Asal -> Tujuan</th><th>Koli</th><th>Berat</th><th>Layanan</th><th>Harga</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $inv->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><a href="/shipments/<?php echo e($item->shipment?->id); ?>" class="text-decoration-none"><?php echo e($item->shipment?->resi_no); ?></a></td>
                    <td><?php echo e($item->shipment?->receiver_name); ?></td>
                    <td><?php echo e($item->shipment?->originBranch?->city); ?> -> <?php echo e($item->shipment?->destinationBranch?->city); ?></td>
                    <td><?php echo e($item->shipment?->total_koli); ?></td>
                    <td><?php echo e($item->shipment?->total_weight); ?> kg</td>
                    <td><?php echo e($item->shipment?->service_type); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
                <tr class="table-active">
                    <td colspan="6" class="text-end"><strong>Total Invoice</strong></td>
                    <td><strong>Rp <?php echo e(number_format($inv->displayTotal(), 0, ',', '.')); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php if($inv->notes): ?>
<div class="mt-4 card">
    <div class="card-header"><strong>Catatan</strong></div>
    <div class="card-body">
        <?php if($inv->parentInvoice): ?>
            Sisa dari invoice <a href="/invoices/<?php echo e($inv->parentInvoice->id); ?>" class="text-decoration-none"><?php echo e($inv->parentInvoice->invoice_no); ?></a>
        <?php else: ?>
            <?php echo e($inv->notes); ?>

        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<div class="mt-4 card">
    <div class="card-header"><strong>Progress Pembayaran</strong></div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-sm-4">Total: <strong>Rp <?php echo e(number_format($inv->total_amount ?? 0, 0, ',', '.')); ?></strong></div>
            <div class="col-sm-4">Dibayar: <strong>Rp <?php echo e(number_format($inv->amount_paid ?? 0, 0, ',', '.')); ?></strong></div>
            <div class="col-sm-4">Sisa: <strong>Rp <?php echo e(number_format(($inv->total_amount ?? 0) - ($inv->amount_paid ?? 0), 0, ',', '.')); ?></strong></div>
        </div>
        <?php $pct = ($inv->total_amount ?? 0) > 0 ? min(100, round(($inv->amount_paid ?? 0) / $inv->total_amount * 100)) : 0; ?>
        <div class="progress mb-3" style="height: 20px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e($pct); ?>%;" aria-valuenow="<?php echo e($pct); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo e($pct); ?>%</div>
        </div>
        <?php if(auth()->user()->canManageInvoices() && $inv->invoice_status !== 'Paid' && $inv->invoice_status !== 'Cancelled' && $inv->childInvoices->count() === 0): ?>
        <form action="/invoices/<?php echo e($inv->id); ?>/payment" method="POST" class="row g-2">
            <?php echo csrf_field(); ?>
            <div class="col-md-3">
                <input type="number" name="amount" class="form-control" step="0.01" placeholder="Jumlah bayar" required>
            </div>
            <div class="col-md-3">
                <input type="date" name="payment_date" class="form-control" value="<?php echo e(date('Y-m-d')); ?>" required>
            </div>
            <div class="col-md-3">
                <select name="payment_method" class="form-select">
                    <option value="transfer">Transfer</option>
                    <option value="cash">Cash</option>
                    <option value="qris">QRIS</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Catat Pembayaran</button>
            </div>
        </form>
        <?php endif; ?>
        <?php if($inv->payments && $inv->payments->count() > 0): ?>
        <h6 class="mt-3">Riwayat Pembayaran</h6>
        <table class="table table-sm">
            <thead><tr><th>Tanggal</th><th>Metode</th><th>Jumlah</th><th>Catatan</th></tr></thead>
            <tbody>
            <?php $__currentLoopData = $inv->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($pay->payment_date); ?></td>
                    <td><?php echo e($pay->payment_method); ?></td>
                    <td>Rp <?php echo e(number_format($pay->amount, 0, ',', '.')); ?></td>
                    <td><?php echo e($pay->notes); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<div class="mt-4 d-flex gap-2">
    <?php if(auth()->user()->canManageInvoices()): ?>
    <?php if($inv->invoice_status !== 'Paid' && $inv->invoice_status !== 'Cancelled' && $inv->childInvoices->count() === 0): ?>
        <form action="/invoices/<?php echo e($inv->id); ?>/mark-paid" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-success">Tandai Lunas</button>
        </form>
    <?php endif; ?>
    <?php if($inv->invoice_status !== 'Paid' && $inv->invoice_status !== 'Cancelled'): ?>
        <form action="/invoices/<?php echo e($inv->id); ?>/mark-cancelled" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-danger">Batalkan</button>
        </form>
    <?php endif; ?>
    <?php endif; ?>
</div>

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
                        // remove cancel/link buttons — user closes via X
                        // remove cancel link — user closes via X button in header
                        var cancel = form.querySelector('.btn-outline-secondary[href]');
                        if (cancel) cancel.remove();
                        document.getElementById('editFormBody').innerHTML = '';
                        document.getElementById('editFormBody').appendChild(form);

                        // Initialize cascading selects with pre-filled values
                        (function() {
                            var provSel = document.getElementById('customer_province');
                            var regSel = document.getElementById('customer_regency');
                            var distSel = document.getElementById('customer_district');
                            var villSel = document.getElementById('customer_village');
                            if (!provSel) return;

                            var selProv = provSel.getAttribute('data-selected') || '';
                            var selReg = regSel ? (regSel.getAttribute('data-selected') || '') : '';
                            var selDist = distSel ? (distSel.getAttribute('data-selected') || '') : '';
                            var selVill = villSel ? (villSel.getAttribute('data-selected') || '') : '';

                            loadOptions('/api/regions/provinces', 'customer_province');

                            if (selProv) {
                                setTimeout(function() {
                                    provSel.value = selProv;
                                    loadOptions('/api/regions/regencies?province=' + selProv, 'customer_regency');
                                    if (selReg) {
                                        setTimeout(function() {
                                            regSel.value = selReg;
                                            loadOptions('/api/regions/districts?regency=' + selReg, 'customer_district');
                                            if (selDist) {
                                                setTimeout(function() {
                                                    distSel.value = selDist;
                                                    loadOptions('/api/regions/villages?district=' + selDist, 'customer_village');
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
                                loadOptions('/api/regions/regencies?province=' + this.value, 'customer_regency');
                                if (distSel) distSel.innerHTML = '<option value="">Pilih...</option>';
                                if (villSel) villSel.innerHTML = '<option value="">Pilih...</option>';
                            });
                            if (regSel) regSel.addEventListener('change', function() {
                                loadOptions('/api/regions/districts?regency=' + this.value, 'customer_district');
                                if (villSel) villSel.innerHTML = '<option value="">Pilih...</option>';
                            });
                            if (distSel) distSel.addEventListener('change', function() {
                                loadOptions('/api/regions/villages?district=' + this.value, 'customer_village');
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/invoices/show.blade.php ENDPATH**/ ?>