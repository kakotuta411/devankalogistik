<?php
require '/var/www/devanka-logistik/vendor/autoload.php';
$app = require_once '/var/www/devanka-logistik/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Shipment;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

echo "=== CUSTOMERS ===\n";
foreach (Customer::all(['id','name','email','phone'])->toArray() as $c) {
    echo sprintf("#%s %s %s %s\n", $c['id'], $c['name'], $c['email'], $c['phone']);
}
echo "\n=== SHIPMENTS ===\n";
foreach (Shipment::all(['id','customer_id','resi_no','shipment_status'])->toArray() as $s) {
    echo sprintf("#%s customer:%s resi:%s status:%s\n", $s['id'], $s['customer_id'], $s['resi_no'], $s['shipment_status']);
}
echo "\n=== INVOICES (using DB to avoid model quirks) ===\n";
$invoices = DB::table('invoices')->get();
foreach ($invoices->toArray() as $i) {
    echo json_encode((array) $i)."\n";
}
echo "\n=== INVOICE TABLE COLUMNS ===\n";
echo implode(', ', DB::getSchemaBuilder()->getColumnListing('invoices'))." \n";
