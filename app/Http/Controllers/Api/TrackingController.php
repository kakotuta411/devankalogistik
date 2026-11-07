<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\TrackingEvent;

class TrackingController extends Controller
{
    public function show($resi)
    {
        $shipment = Shipment::withoutGlobalScopes()
            ->where('resi_no', $resi)
            ->first();

        if (!$shipment) {
            return response()->json(['error' => 'Resi tidak ditemukan'], 404);
        }

        // Public tracking — limited info per client request
        $receiverName = null;
        $deliveredAt = null;
        if ($shipment->shipment_status === 'Delivered') {
            $lastEvent = $shipment->trackingEvents()->where('event_type', 'Delivered')->orderBy('event_time', 'desc')->first();
            $receiverName = $shipment->receiver_name;
            $deliveredAt = $lastEvent?->event_time;
        }
        return response()->json([
            'resi_no' => $shipment->resi_no,
            'entry_date' => $shipment->created_at?->format('d/m/Y H:i'),
            'sender' => $shipment->sender_name,
            'receiver' => $shipment->receiver_name,
            'shipment_status' => $shipment->shipment_status,
            'tracking_events' => $shipment->trackingEvents()
                ->select('event_type', 'location', 'event_time', 'notes')
                ->orderBy('event_time', 'asc')
                ->get()
                ->map(function ($event) {
                    return [
                        'event_type' => $event->event_type,
                        'location' => $event->location,
                        'event_time' => $event->event_time?->format('d/m/Y H:i'),
                        'notes' => $event->notes,
                    ];
                }),
            'receiver_name' => $receiverName,
            'delivered_at' => $deliveredAt ? $deliveredAt->format('d/m/Y H:i') : null,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('resi', '');
        if (strlen($query) < 3) {
            return response()->json(['error' => 'Minimal 3 karakter'], 400);
        }
        $shipment = Shipment::withoutGlobalScopes()
            ->where('resi_no', 'like', "%$query%")
            ->first();
        if (!$shipment) {
            return response()->json(['error' => 'Resi tidak ditemukan'], 404);
        }
        return $this->show($shipment->resi_no);
    }
}
