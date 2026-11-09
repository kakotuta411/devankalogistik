<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo '=== TABEL DENGAN KOLOM _code ===' . PHP_EOL;
$cols = DB::select("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME LIKE '%_code' ORDER BY TABLE_NAME, COLUMN_NAME");
foreach ($cols as $c) {
    echo $c->TABLE_NAME . '.' . $c->COLUMN_NAME . PHP_EOL;
}

echo PHP_EOL . '=== DATA NON-NULL ===' . PHP_EOL;
$queries = [
    'customers' => "SELECT id, name, province_code, regency_code, district_code, village_code FROM customers WHERE province_code IS NOT NULL",
    'shipments' => "SELECT id, resi_no, sender_province_code, sender_regency_code, sender_district_code, sender_village_code, receiver_province_code, receiver_regency_code, receiver_district_code, receiver_village_code FROM shipments WHERE sender_province_code IS NOT NULL OR receiver_province_code IS NOT NULL",
    'invoices' => "SELECT id, invoice_no, customer_province_code, customer_regency_code, customer_district_code, customer_village_code FROM invoices WHERE customer_province_code IS NOT NULL",
    'branches' => "SELECT id, name, province_code, regency_code, district_code, village_code FROM branches WHERE province_code IS NOT NULL",
];
foreach ($queries as $table => $sql) {
    $rows = DB::select($sql);
    echo $table . ': ' . count($rows) . ' rows' . PHP_EOL;
    foreach ($rows as $r) {
        echo '  ' . json_encode($r) . PHP_EOL;
    }
}
