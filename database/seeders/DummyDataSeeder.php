<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Use existing branches (JKT + BTM only)
        $jkt = Branch::where('branch_code', 'JKT')->first();
        $btm = Branch::where('branch_code', 'BTM')->first();

        if (!$jkt || !$btm) {
            echo "ERROR: JKT or BTM branch not found. Run base seeder first.\n";
            return;
        }

        $admin = User::where('email', 'admin@logistik.test')->first();
        $direktur = User::where('email', 'user4@logistik.test')->first();

        if (!$admin) {
            echo "ERROR: admin user not found.\n";
            return;
        }

        $actorId = $admin->id;

        // Clean up existing transactional data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ManifestItem::truncate();
        TrackingEvent::truncate();
        Shipment::withoutGlobalScopes()->truncate();
        Manifest::withoutGlobalScopes()->truncate();
        Customer::truncate();

        // Seed customers
        $customers = [
            ['name' => 'PT Maju Jaya', 'company_name' => 'PT Maju Jaya', 'phone' => '0211234567', 'email' => 'info@majujaya.com', 'address' => 'Jl. Sudirman No. 1, Jakarta'],
            ['name' => 'CV Berkah Abadi', 'company_name' => 'CV Berkah Abadi', 'phone' => '0219876543', 'email' => 'berkah@gmail.com', 'address' => 'Jl. Gatot Subroto No. 5, Jakarta'],
            ['name' => 'PT Global Logistik', 'company_name' => 'PT Global Logistik', 'phone' => '0215551234', 'email' => 'global@logistik.com', 'address' => 'Jl. Thamrin No. 10, Jakarta'],
        ];
        $custModels = [];
        foreach ($customers as $c) {
            $custModels[] = Customer::create($c);
        }
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $shipmentNo = 1;
        $manifestNo = 1;

        // Helper to create shipment
        $createShipment = function (string $origin, string $dest, string $status, string $service = 'REGULER') use (&$jkt, &$btm, &$shipmentNo, $actorId, &$custModels) {
            $originBranch = $origin === 'JKT' ? $jkt : $btm;
            $destBranch = $dest === 'JKT' ? $jkt : $btm;

            $shipment = Shipment::withoutGlobalScopes()->create([
                'resi_no' => 'DLI-20261104-' . str_pad($shipmentNo, 5, '0', STR_PAD_LEFT),
                'origin_branch_id' => $originBranch->id,
                'destination_branch_id' => $destBranch->id,
                'sender_name' => 'Pengirim ' . $shipmentNo,
                'sender_phone' => '0812' . str_pad($shipmentNo, 8, '0', STR_PAD_LEFT),
                'sender_address' => 'Jl. Pengirim No. ' . $shipmentNo,
                'receiver_name' => 'Penerima ' . $shipmentNo,
                'receiver_phone' => '0877' . str_pad($shipmentNo, 8, '0', STR_PAD_LEFT),
                'receiver_address' => 'Jl. Penerima No. ' . $shipmentNo,
                'total_koli' => rand(1, 5),
                'total_weight' => rand(2, 15) + 0.5,
                'customer_id' => $custModels[array_rand($custModels)]->id,
                'po_number' => 'PO-2026-' . str_pad(rand(1, 5), 3, '0', STR_PAD_LEFT),
                'payment_type' => ['kredit', 'lunas', 'cod'][array_rand(['kredit', 'lunas', 'cod'])],
                'service_type' => $service,
                'package_description' => 'Paket ' . $service . ' - ' . rand(1, 10) . ' item',
                'shipment_status' => $status,
            ]);

            // Create tracking event for current status
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'event_type' => $status,
                'location' => $originBranch->city,
                'event_time' => now()->subDays(rand(0, 3)),
                'actor_id' => $actorId,
                'notes' => 'Status: ' . $status,
            ]);

            $shipmentNo++;
            return $shipment;
        };

        // Helper to create manifest with shipments
        $createManifest = function (string $origin, string $dest, string $status, array $shipments) use (&$jkt, &$btm, &$manifestNo, $actorId) {
            $originBranch = $origin === 'JKT' ? $jkt : $btm;
            $destBranch = $dest === 'JKT' ? $jkt : $btm;

            $manifest = Manifest::withoutGlobalScopes()->create([
                'manifest_no' => 'AKL-MNF-20261104-' . str_pad($manifestNo, 5, '0', STR_PAD_LEFT),
                'origin_branch_id' => $originBranch->id,
                'destination_branch_id' => $destBranch->id,
                'vehicle_no' => 'B ' . rand(1000, 9999) . ' XY',
                'manifest_status' => $status,
                'dispatch_time' => in_array($status, ['Dispatched', 'Received', 'Completed']) ? now()->subDays(rand(1, 3)) : null,
                'receive_time' => in_array($status, ['Received', 'Completed']) ? now()->subDays(rand(0, 2)) : null,
                'driver_name' => 'Driver ' . $manifestNo,
            ]);

            foreach ($shipments as $s) {
                ManifestItem::create([
                    'manifest_id' => $manifest->id,
                    'shipment_id' => $s->id,
                ]);
            }

            $manifestNo++;
            return $manifest;
        };

        // === Manifest 1: Draft (JKT→BTM) — 2 Created shipments ===
        $s1 = $createShipment('JKT', 'BTM', 'Created', 'REGULER');
        $s2 = $createShipment('JKT', 'BTM', 'Created', 'EXPRESS');
        $createManifest('JKT', 'BTM', 'Draft', [$s1, $s2]);

        // === Manifest 2: Dispatched (JKT→BTM) — 2 In Transit shipments ===
        $s3 = $createShipment('JKT', 'BTM', 'In Transit', 'REGULER');
        $s4 = $createShipment('JKT', 'BTM', 'In Transit', 'EXPRESS');
        $createManifest('JKT', 'BTM', 'Dispatched', [$s3, $s4]);

        // === Manifest 3: Received (BTM→JKT) — 2 Arrived at Destination shipments ===
        $s5 = $createShipment('BTM', 'JKT', 'Arrived at Destination', 'REGULER');
        $s6 = $createShipment('BTM', 'JKT', 'Arrived at Destination', 'EXPRESS');
        $createManifest('BTM', 'JKT', 'Received', [$s5, $s6]);

        // === Manifest 4: Completed (BTM→JKT) — 2 Delivered shipments ===
        $s7 = $createShipment('BTM', 'JKT', 'Delivered', 'REGULER');
        $s8 = $createShipment('BTM', 'JKT', 'Delivered', 'EXPRESS');
        $createManifest('BTM', 'JKT', 'Completed', [$s7, $s8]);

        // === Standalone shipments (no manifest) ===
        $createShipment('JKT', 'BTM', 'On Hold', 'REGULER');
        $createShipment('BTM', 'JKT', 'On Hold', 'EXPRESS');
        $createShipment('JKT', 'BTM', 'Cancelled', 'REGULER');
        $createShipment('BTM', 'JKT', 'Cancelled', 'EXPRESS');

        echo "Dummy data seeded: 12 shipments (8 in manifests, 4 standalone), 4 manifests\n";
        echo "Statuses: Created, In Transit, Arrived at Destination, Delivered, On Hold, Cancelled\n";
        echo "Manifest: Draft, Dispatched, Received, Completed\n";
    }
}
