<?php
// One-off: generate manifest PDF to public/ for download
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$manifest = \App\Models\Manifest::withoutGlobalScopes()->first();
if (!$manifest) { echo "no manifest\n"; exit(1); }
$manifest->load(['items', 'originBranch', 'destinationBranch']);
$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('print.manifest-sheet', compact('manifest'))->setPaper('a4');
file_put_contents('/tmp/prod-manifest.pdf', $pdf->output());
echo "OK: {$manifest->manifest_no}\n";
