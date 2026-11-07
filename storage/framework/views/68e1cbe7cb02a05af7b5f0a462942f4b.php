<?php $__env->startSection('page_header'); ?>
    <h1 class="h2">Dashboard</h1>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('toolbar'); ?>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="input-group input-group-sm" style="max-width:280px;">
            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
            <input type="date" class="form-control form-control-sm" id="startDate"
                value="<?php echo e($stats['date_range']['start_date']); ?>">
            <span class="input-group-text">to</span>
            <input type="date" class="form-control form-control-sm" id="endDate"
                value="<?php echo e($stats['date_range']['end_date']); ?>">
        </div>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary" id="presetBtn" data-bs-toggle="dropdown">
                <i class="bi bi-funnel"></i> Presets
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item preset-opt" href="#" data-days="7">Last 7 Days</a></li>
                <li><a class="dropdown-item preset-opt" href="#" data-days="30">Last 30 Days</a></li>
                <li><a class="dropdown-item preset-opt active" href="#" data-days="this_month">This Month</a></li>
                <li><a class="dropdown-item preset-opt" href="#" data-days="0">All Time (max 90d)</a></li>
            </ul>
            <button type="button" class="btn btn-outline-secondary" id="applyFilter">
                <i class="bi bi-check2"></i> Apply
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php
        function sortLink($col, $label)
        {
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
    <div class="row g-3 mb-3">
        <div class="col ">
            <div class="card text-bg-success mb-3" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalShipments">
                <div class="card-header">Shipments</div>
                <div class="card-body text-center">
                    <h5 class="card-title"><?php echo e($stats['shipments_today']); ?></h5>
                    <p class="card-text small">Total shipments</p>
                </div>
            </div>
        </div>
        <div class="col ">
            <div class="card text-bg-success mb-3" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalManifests">
                <div class="card-header">Manifests</div>
                <div class="card-body text-center">
                    <h5 class="card-title"><?php echo e($stats['manifests_today']); ?></h5>
                    <p class="card-text small">Total manifests</p>
                </div>
            </div>
        </div>
        <div class="col ">
            <div class="card text-bg-success mb-3" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalInvoices">
                <div class="card-header">Invoices</div>
                <div class="card-body text-center">
                    <h5 class="card-title"><?php echo e($stats['invoices_today']); ?></h5>
                    <p class="card-text small">Total invoices</p>
                </div>
            </div>
        </div>
        <div class="col ">
            <div class="card text-bg-success mb-3">
                <div class="card-header">Invoice Lunas</div>
                <div class="card-body text-center">
                    <h5 class="card-title"><?php echo e($stats['invoices_paid']); ?></h5>
                    <p class="card-text small"><?php echo e(number_format($stats['invoices_paid_amount'], 0, ',', '.')); ?></p>
                </div>
            </div>
        </div>
        <div class="col ">
            <div class="card text-bg-warning mb-3">
                <div class="card-header">Invoice Pending</div>
                <div class="card-body text-center">
                    <h5 class="card-title"><?php echo e($stats['invoices_pending']); ?></h5>
                    <p class="card-text small"><?php echo e(number_format($stats['invoices_pending_amount'], 0, ',', '.')); ?></p>
                </div>
            </div>
        </div>
        <!-- <div class="col ">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h5 class="card-title"><?php echo e($stats['branches']); ?></h5>
                                                            <p class="card-text text-body-secondary small">Total Branches</p>
                                                        </div>
                                                    </div>
                                                </div> -->
    </div>

    <div class="card">
        <div class="card-header"><strong>Recent Shipments</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col"><?php echo sortLink('resi_no', 'Resi'); ?></th>
                            <th scope="col"><?php echo sortLink('origin_city', 'Asal'); ?></th>
                            <th scope="col"><?php echo sortLink('destination_city', 'Tujuan'); ?></th>
                            <th scope="col"><?php echo sortLink('shipment_status', 'Status'); ?></th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $stats['recent_shipments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $statusClasses = ['Created' => 'secondary', 'In Transit' => 'info', 'Arrived at Transit' => 'warning', 'Arrived at Destination' => 'primary', 'Out for Delivery' => 'warning', 'Delivered' => 'success', 'On Hold' => 'warning', 'Cancelled' => 'danger'];
                            ?>
                            <tr>
                                <td><a href="/shipments/<?php echo e($s->id); ?>"><?php echo e($s->resi_no); ?></a></td>
                                <td><?php echo e($s->originBranch?->city ?? '-'); ?></td>
                                <td><?php echo e($s->destinationBranch?->city ?? '-'); ?></td>
                                <td><span
                                        class="badge text-bg-<?php echo e($statusClasses[$s->shipment_status] ?? 'secondary'); ?>"><?php echo e($s->shipment_status); ?></span>
                                </td>
                                <td><a href="/print/shipments/<?php echo e($s->id); ?>/label" target="_blank"
                                        class="text-decoration-none"><i class="bi bi-printer"></i></a></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($stats['recent_shipments']->isEmpty()): ?>
                            <tr>
                                <td colspan="5" class="text-center text-body-secondary py-3">No shipments in selected date range
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Shipments -->
    <div class="modal fade" id="modalShipments" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Shipments (<?php echo e($stats['shipments_today']); ?>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Resi</th>
                                    <th>Asal</th>
                                    <th>Tujuan</th>
                                    <th>Status</th>
                                    <th>Layanan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $stats['recent_shipments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $sc = ['Created'=>'secondary','In Transit'=>'info','Arrived at Destination'=>'primary','Delivered'=>'success','On Hold'=>'warning','Cancelled'=>'danger'];
                                    ?>
                                    <tr>
                                        <td><a href="/shipments/<?php echo e($s->id); ?>"><?php echo e($s->resi_no); ?></a></td>
                                        <td><?php echo e($s->originBranch?->city ?? '-'); ?></td>
                                        <td><?php echo e($s->destinationBranch?->city ?? '-'); ?></td>
                                        <td><span class="badge text-bg-<?php echo e($sc[$s->shipment_status] ?? 'secondary'); ?>"><?php echo e($s->shipment_status); ?></span></td>
                                        <td><?php echo e($s->service_type); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php if($stats['recent_shipments']->isEmpty()): ?>
                                    <tr><td colspan="5" class="text-center text-body-secondary py-3">No shipments</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/shipments" class="btn btn-sm btn-primary">Lihat Semua</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Manifests -->
    <div class="modal fade" id="modalManifests" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manifests (<?php echo e($stats['manifests_today']); ?>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No Manifest</th>
                                    <th>Asal</th>
                                    <th>Tujuan</th>
                                    <th>Status</th>
                                    <th>Kendaraan</th>
                                    <th>Driver</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $stats['recent_manifests']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $mc = ['Draft'=>'secondary','Dispatched'=>'warning','Received'=>'info','Completed'=>'success'];
                                    ?>
                                    <tr>
                                        <td><a href="/manifests/<?php echo e($m->id); ?>"><?php echo e($m->manifest_no); ?></a></td>
                                        <td><?php echo e($m->originBranch?->city ?? '-'); ?></td>
                                        <td><?php echo e($m->destinationBranch?->city ?? '-'); ?></td>
                                        <td><span class="badge text-bg-<?php echo e($mc[$m->manifest_status] ?? 'secondary'); ?>"><?php echo e($m->manifest_status); ?></span></td>
                                        <td><?php echo e($m->vehicle_no ?? '-'); ?></td>
                                        <td><?php echo e($m->driver_name ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php if($stats['recent_manifests']->isEmpty()): ?>
                                    <tr><td colspan="6" class="text-center text-body-secondary py-3">No manifests</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/manifests" class="btn btn-sm btn-primary">Lihat Semua</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Invoices -->
    <div class="modal fade" id="modalInvoices" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invoices (<?php echo e($stats['invoices_today']); ?>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No Invoice</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Subtotal</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $stats['recent_invoices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $ic = ['Draft'=>'secondary','Paid'=>'success','Cancelled'=>'danger'];
                                    ?>
                                    <tr>
                                        <td><a href="/invoices/<?php echo e($inv->id); ?>"><?php echo e($inv->invoice_no); ?></a></td>
                                        <td><?php echo e($inv->customer_name); ?></td>
                                        <td><span class="badge text-bg-<?php echo e($ic[$inv->invoice_status] ?? 'secondary'); ?>"><?php echo e($inv->invoice_status); ?></span></td>
                                        <td>Rp <?php echo e(number_format($inv->subtotal, 0, ',', '.')); ?></td>
                                        <td><?php echo e($inv->created_at?->format('d/m/Y')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php if($stats['recent_invoices']->isEmpty()): ?>
                                    <tr><td colspan="5" class="text-center text-body-secondary py-3">No invoices</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/invoices" class="btn btn-sm btn-primary">Lihat Semua</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var startDate = document.getElementById('startDate');
            var endDate = document.getElementById('endDate');
            var applyBtn = document.getElementById('applyFilter');
            // Limit endDate to 90 days from startDate
            startDate.addEventListener('change', function () {
                var start = new Date(startDate.value);
                var maxEnd = new Date(start);
                maxEnd.setDate(start.getDate() + 90);
                endDate.min = startDate.value;
                endDate.max = maxEnd.toISOString().split('T')[0];
            });

            // Apply filter
            applyBtn.addEventListener('click', function () {
                var qs = new URLSearchParams(window.location.search);
                qs.set('start_date', startDate.value);
                qs.set('end_date', endDate.value);
                qs.delete('sort');
                qs.delete('dir');
                window.location.search = qs.toString();
            });

            // Preset options
            document.querySelectorAll('.preset-opt').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    var days = el.getAttribute('data-days');
                    var now = new Date();
                    if (days === 'this_month') {
                        var first = new Date(now.getFullYear(), now.getMonth(), 1);
                        startDate.value = first.toISOString().split('T')[0];
                    } else if (days === '0') {
                        var d = new Date(now);
                        d.setDate(now.getDate() - 90);
                        startDate.value = d.toISOString().split('T')[0];
                    } else {
                        var d = new Date(now);
                        d.setDate(now.getDate() - parseInt(days));
                        startDate.value = d.toISOString().split('T')[0];
                    }
                    endDate.value = now.toISOString().split('T')[0];
                    // Trigger apply
                    applyBtn.click();
                });
            });

            // Enter key on date inputs = apply
            [startDate, endDate].forEach(function (inp) {
                inp.addEventListener('keypress', function (e) {
                    if (e.which === 13) { applyBtn.click(); }
                });
            });

        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard.blade.php ENDPATH**/ ?>