<?php
// Check counts
require '/var/www/devanka-logistik/vendor/autoload.php';
$app = require_once '/var/www/devanka-logistik/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Models\Shipment;
use App\Models\Invoice;

echo "=== COUNTS ===\n";
echo "Customers: ".Customer::count()." \n";
echo "Shipments: ".Shipment::count()." \n";
echo "Invoices: ".Invoice::count()." \n";
echo "=== CUSTOMERS ===\n";
foreach (Customer::all(['id','name'])->toArray() as $c) {
    echo $c['id'].' | '.$c['name']." \n";
}
