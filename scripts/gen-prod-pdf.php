<?php
// One-off: generate production resi PDFs to public/ for download
$s = \App\Models\Shipment::withoutGlobalScopes()->first();
if (!$s) { echo "no shipment\n"; exit(1); }
$s->load(['originBranch', 'destinationBranch', 'customer']);

$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('print.resi-detail', compact('s'))->setPaper([0, 0, 684, 396]);
file_put_contents('/var/www/devanka-logistik/public/prod-resi-detail.pdf', $pdf->output());

$barcodeBase64 = \App\Helpers\BarcodeGenerator::code128($s->resi_no);
$pdf2 = \Barryvdh\DomPDF\Facade\Pdf::loadView('print.resi-label', compact('s', 'barcodeBase64'))->setPaper([0, 0, 283.46, 283.46]);
file_put_contents('/var/www/devanka-logistik/public/prod-resi-label.pdf', $pdf2->output());

echo "OK: resi {$s->resi_no}\n";
