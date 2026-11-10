<?php
// One-off: generate production resi PDFs to public/ for download
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$shipment = \App\Models\Shipment::withoutGlobalScopes()->first();
if (!$shipment) { echo "no shipment\n"; exit(1); }
$shipment->load(['originBranch', 'destinationBranch', 'customer']);

$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('print.resi-detail', compact('shipment'))->setPaper([0, 0, 684, 396]);
file_put_contents('/var/www/devanka-logistik/public/prod-resi-detail.pdf', $pdf->output());

$barcodeBase64 = \App\Helpers\BarcodeGenerator::code128($shipment->resi_no);
$pdf2 = \Barryvdh\DomPDF\Facade\Pdf::loadView('print.resi-label', compact('shipment', 'barcodeBase64'))->setPaper([0, 0, 283.46, 283.46]);
file_put_contents('/var/www/devanka-logistik/public/prod-resi-label.pdf', $pdf2->output());

echo "OK: resi {$shipment->resi_no}\n";
