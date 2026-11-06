<?php $__env->startSection('title', 'Template Management'); ?>
<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
<div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<!-- Settings Form -->
<div class="card mb-4">
    <div class="card-header"><strong>Pengaturan Template Dokumen</strong></div>
    <div class="card-body">
        <form action="/admin/template-management" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="doc_type" value="invoice" id="doc_type_input">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Terapkan untuk *</label>
                    <select name="doc_type_select" id="doc_type_select" class="form-select" required>
                        <option value="invoice">Invoice</option>
                        <option value="manifest">Manifest Sheet</option>
                        <option value="shipment">Resi Label</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nama Perusahaan</label>
                    <input type="text" name="company_name" class="form-control" id="company_name" placeholder="Logistik Tracking System">
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="form-label">Format Kertas</label>
                    <select name="paper_format" id="paper_format" class="form-select">
                        <option value="A4">A4</option>
                        <option value="Letter">Letter</option>
                        <option value="thermal">Thermal 10x10cm</option>
                        <option value="dotmatrix-half">Dot Matrix Half (9.5x5.5")</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Logo Perusahaan</label>
                    <input type="file" name="company_logo" id="company_logo" accept="image/*" class="form-control">
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Header Text</label>
                <input type="text" name="header_text" id="header_text" class="form-control" placeholder="Dokumen ini dicetak secara otomatis">
            </div>
            <div class="mt-3">
                <label class="form-label">Footer Text</label>
                <input type="text" name="footer_text" id="footer_text" class="form-control" placeholder="Logistik Tracking System — Confidential">
            </div>
            <div class="mt-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-warning"><i class="bi bi-save me-2"></i>Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>

<!-- 3 Cards: Invoice, Manifest, Shipment -->
<div class="row g-3">
    <!-- Invoice Card -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong>Invoice</strong></div>
            <div class="card-body">
                <p class="mb-1"><strong>Company:</strong> <?php echo e($allSettings['invoice']['company_name'] ?? '-'); ?></p>
                <p class="mb-1"><strong>Kertas:</strong> <?php echo e($allSettings['invoice']['paper_format'] ?? '-'); ?></p>
                <p class="mb-1"><strong>Header:</strong> <?php echo e($allSettings['invoice']['header_text'] ?? '-'); ?></p>
                <p class="mb-1"><strong>Footer:</strong> <?php echo e($allSettings['invoice']['footer_text'] ?? '-'); ?></p>
                <?php if(!empty($allSettings['invoice']['company_logo'])): ?>
                    <img src="<?php echo e(Storage::url($allSettings['invoice']['company_logo'])); ?>" class="mt-2" style="max-height: 60px;" alt="Logo">
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <?php if($firstInvoice): ?>
                <button type="button" class="btn btn-sm btn-outline-success w-100 preview-btn" data-preview-url="/print/invoices/<?php echo e($firstInvoice->id); ?>/pdf"><i class="bi bi-eye me-1"></i>Preview</button>
                <?php else: ?>
                <span class="text-muted small">Belum ada invoice</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Manifest Card -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong>Manifest Sheet</strong></div>
            <div class="card-body">
                <p class="mb-1"><strong>Company:</strong> <?php echo e($allSettings['manifest']['company_name'] ?? '-'); ?></p>
                <p class="mb-1"><strong>Kertas:</strong> <?php echo e($allSettings['manifest']['paper_format'] ?? '-'); ?></p>
                <p class="mb-1"><strong>Header:</strong> <?php echo e($allSettings['manifest']['header_text'] ?? '-'); ?></p>
                <p class="mb-1"><strong>Footer:</strong> <?php echo e($allSettings['manifest']['footer_text'] ?? '-'); ?></p>
                <?php if(!empty($allSettings['manifest']['company_logo'])): ?>
                    <img src="<?php echo e(Storage::url($allSettings['manifest']['company_logo'])); ?>" class="mt-2" style="max-height: 60px;" alt="Logo">
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <?php if($firstManifest): ?>
                <button type="button" class="btn btn-sm btn-outline-success w-100 preview-btn" data-preview-url="/print/manifests/<?php echo e($firstManifest->id); ?>/sheet"><i class="bi bi-eye me-1"></i>Preview</button>
                <?php else: ?>
                <span class="text-muted small">Belum ada manifest</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Shipment Card -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong>Resi Label</strong></div>
            <div class="card-body">
                <p class="mb-1"><strong>Company:</strong> <?php echo e($allSettings['shipment']['company_name'] ?? '-'); ?></p>
                <p class="mb-1"><strong>Kertas:</strong> <?php echo e($allSettings['shipment']['paper_format'] ?? '-'); ?></p>
                <p class="mb-1"><strong>Header:</strong> <?php echo e($allSettings['shipment']['header_text'] ?? '-'); ?></p>
                <p class="mb-1"><strong>Footer:</strong> <?php echo e($allSettings['shipment']['footer_text'] ?? '-'); ?></p>
                <?php if(!empty($allSettings['shipment']['company_logo'])): ?>
                    <img src="<?php echo e(Storage::url($allSettings['shipment']['company_logo'])); ?>" class="mt-2" style="max-height: 60px;" alt="Logo">
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <?php if($firstShipment): ?>
                <button type="button" class="btn btn-sm btn-outline-success w-100 preview-btn" data-preview-url="/print/shipments/<?php echo e($firstShipment->id); ?>/label"><i class="bi bi-eye me-1"></i>Preview</button>
                <?php else: ?>
                <span class="text-muted small">Belum ada shipment</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Extra Large Modal for Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="previewIframe" src="" width="100%" height="600" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <a id="previewFullLink" href="" target="_blank" class="btn btn-sm btn-outline-secondary">Buka di Tab Baru</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var allSettings = <?php echo json_encode($allSettings, 15, 512) ?>;
    var docTypeSelect = document.getElementById('doc_type_select');
    var docTypeInput = document.getElementById('doc_type_input');
    var companyNameInput = document.getElementById('company_name');
    var paperFormatSelect = document.getElementById('paper_format');
    var headerTextInput = document.getElementById('header_text');
    var footerTextInput = document.getElementById('footer_text');

    function loadSettings(type) {
        var s = allSettings[type] || {};
        docTypeInput.value = type;
        companyNameInput.value = s.company_name || '';
        headerTextInput.value = s.header_text || '';
        footerTextInput.value = s.footer_text || '';
        paperFormatSelect.value = s.paper_format || 'A4';
    }

    docTypeSelect.addEventListener('change', function() {
        loadSettings(this.value);
    });

    // Load default (invoice) on page load
    loadSettings('invoice');

    // Preview modal
    var previewModal = document.getElementById('previewModal');
    var iframe = document.getElementById('previewIframe');
    var fullLink = document.getElementById('previewFullLink');

    document.querySelectorAll('.preview-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = this.getAttribute('data-preview-url');
            iframe.src = url;
            fullLink.href = url;
            var modal = new bootstrap.Modal(previewModal);
            modal.show();
        });
    });

    previewModal.addEventListener('hidden.bs.modal', function() {
        iframe.src = '';
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/template-management.blade.php ENDPATH**/ ?>