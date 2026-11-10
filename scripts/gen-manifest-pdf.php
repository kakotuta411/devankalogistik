<?php
// One-off: generate manifest PDF to public/ for download
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$m = \App\Models\Manifest::withoutGlobalScopes()->first();
if (!$m) { echo "no manifest\n"; exit(1); }
$m->load(['items', 'originBranch', 'destinationBranch']);
$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('print.manifest-sheet', compact('m'))->setPaper('a4');
file_put_contents('/tmp/prod-manifest.pdf', $pdf->output());
echo "OK: {$m->manifest_no}\n";
