<?php $__env->startSection('title', 'Purchase Orders'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Purchase Orders</h4>
    </div>

    <!-- Cards -->
    <div class="row mb-3 g-2">
        <div class="col">
            <div class="card text-bg-primary" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#poActiveModal">
                <div class="card-header text-center">PO Aktif</div>
                <div class="card-body text-center">
                    <h5><?php echo e($activePos->count()); ?></h5>
                    <small><?php echo e(number_format($activePos->sum('total_amount'), 0, ',', '.')); ?></small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-secondary" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#poDraftModal">
                <div class="card-header text-center">PO Draft</div>
                <div class="card-body text-center">
                    <h5><?php echo e($draftPos->count()); ?></h5>
                    <small><?php echo e(number_format($draftPos->sum('total_amount'), 0, ',', '.')); ?></small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-warning" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#poAllModal">
                <div class="card-header text-center">PO Total</div>
                <div class="card-body text-center">
                    <h5><?php echo e($allPos->count()); ?></h5>
                    <small><?php echo e(number_format($allPos->sum('total_amount'), 0, ',', '.')); ?></small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-success" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#poPaidModal">
                <div class="card-header text-center">PO Lunas</div>
                <div class="card-body text-center">
                    <h5><?php echo e($paidPos->count()); ?></h5>
                    <small><?php echo e(number_format($paidPos->sum('total_amount'), 0, ',', '.')); ?></small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-bg-danger" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#poOverdueModal">
                <div class="card-header text-center">PO Tertunggak</div>
                <div class="card-body text-center">
                    <h5><?php echo e($overduePos->count()); ?></h5>
                    <small><?php echo e(number_format($overduePos->sum(fn($p) => $p->getRemainingAmount()), 0, ',', '.')); ?></small>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari PO / customer..." value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="draft" <?php echo e(request('status')=='draft'?'selected':''); ?>>Draft</option>
                <option value="active" <?php echo e(request('status')=='active'?'selected':''); ?>>Aktif</option>
                <option value="partial" <?php echo e(request('status')=='partial'?'selected':''); ?>>Partial</option>
                <option value="paid" <?php echo e(request('status')=='paid'?'selected':''); ?>>Lunas</option>
                <option value="cancelled" <?php echo e(request('status')=='cancelled'?'selected':''); ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead>
                <tr>
                    <th>No. PO</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Dibayar</th>
                    <th>Sisa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $pos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><a href="<?php echo e(route('purchase-orders.show', $po)); ?>"><?php echo e($po->po_number); ?></a></td>
                    <td><?php echo e($po->customer?->company_name ?? $po->customer?->name ?? '-'); ?></td>
                    <td><?php echo e(number_format($po->total_amount, 0, ',', '.')); ?></td>
                    <td><?php echo e(number_format($po->paid_amount, 0, ',', '.')); ?></td>
                    <td><?php echo e(number_format($po->getRemainingAmount(), 0, ',', '.')); ?></td>
                    <td><span class="badge <?php echo e($po->getStatusBadgeClass()); ?>"><?php echo e(ucfirst($po->status)); ?></span></td>
                    <td>
                        <a href="<?php echo e(route('purchase-orders.show', $po)); ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center text-muted">Belum ada PO</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo e($pos->links()); ?>

</div>

<!-- PO Modals -->
<?php
    $poModals = [
        'poAllModal' => ['title' => 'Semua PO', 'items' => $allPos, 'badge' => 'secondary'],
        'poActiveModal' => ['title' => 'PO Aktif', 'items' => $activePos, 'badge' => 'primary'],
        'poDraftModal' => ['title' => 'PO Draft', 'items' => $draftPos, 'badge' => 'secondary'],
        'poPaidModal' => ['title' => 'PO Lunas', 'items' => $paidPos, 'badge' => 'success'],
        'poOverdueModal' => ['title' => 'PO Tertunggak', 'items' => $overduePos, 'badge' => 'danger'],
    ];
?>
<?php $__currentLoopData = $poModals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modalId => $modal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo e($modal['title']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. PO</th>
                                <th>Customer</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Dibayar</th>
                                <th class="text-end">Sisa</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $modal['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><a href="<?php echo e(route('purchase-orders.show', $po)); ?>" class="fw-medium text-decoration-none"><?php echo e($po->po_number); ?></a></td>
                                <td><?php echo e($po->customer?->company_name ?? $po->customer?->name ?? '-'); ?></td>
                                <td class="text-end"><?php echo e(number_format($po->total_amount, 0, ',', '.')); ?></td>
                                <td class="text-end"><?php echo e(number_format($po->paid_amount, 0, ',', '.')); ?></td>
                                <td class="text-end"><?php echo e(number_format($po->getRemainingAmount(), 0, ',', '.')); ?></td>
                                <td><span class="badge <?php echo e($po->getStatusBadgeClass()); ?>"><?php echo e(ucfirst($po->status)); ?></span></td>
                                <td class="text-end"><a href="<?php echo e(route('purchase-orders.show', $po)); ?>" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="text-center text-muted">Tidak ada data</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/purchase-orders/index.blade.php ENDPATH**/ ?>