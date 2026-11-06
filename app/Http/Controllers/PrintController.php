<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Manifest;
use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    public function invoice($id)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($id);
        $invoice->load(['branch', 'items.shipment' => fn($q) => $q->with(['originBranch', 'destinationBranch'])]);
        $pdf = Pdf::loadView('print.invoice', compact('invoice'))->setPaper('a4');
        return $pdf->stream('invoice-' . $invoice->invoice_no . '.pdf');
    }

    public function shipmentLabel($id)
    {
        $shipment = Shipment::withoutGlobalScopes()->findOrFail($id);
        // Generate QR code locally using chillerlan/php-qrcode
        $options = new \chillerlan\QRCode\QROptions([
            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
        ]);
        $qr = new \chillerlan\QRCode\QRCode($options);
        $qrBase64 = $qr->render($shipment->resi_no);
        $pdf = Pdf::loadView('print.resi-label', compact('shipment', 'qrBase64'))->setPaper([0, 0, 283.46, 283.46]); // 10x10cm
        return $pdf->stream('label-' . $shipment->resi_no . '.pdf');
    }

    public function shipmentResi($id)
    {
        $shipment = Shipment::withoutGlobalScopes()->findOrFail($id);
        $shipment->load(['originBranch', 'destinationBranch', 'customer']);
        $pdf = Pdf::loadView('print.resi-detail', compact('shipment'))->setPaper('a4');
        return $pdf->stream('resi-detail-' . $shipment->resi_no . '.pdf');
    }

    public function manifestSheet($id)
    {
        $manifest = Manifest::withoutGlobalScopes()->findOrFail($id);
        $manifest->load(['items', 'originBranch', 'destinationBranch']);
        $pdf = Pdf::loadView('print.manifest-sheet', compact('manifest'))->setPaper('a4');
        return $pdf->stream('manifest-' . $manifest->manifest_no . '.pdf');
    }

    public function shipmentList()
    {
        $shipments = Shipment::with(['originBranch', 'destinationBranch'])->get();
        $pdf = Pdf::loadView('print.shipment-list', ['shipments' => $shipments, 'generated_at' => now()])->setPaper('a4');
        return $pdf->stream('shipment-list.pdf');
    }
}
