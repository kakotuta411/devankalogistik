<?php

namespace App\Console\Commands;

use App\Models\Region;
use App\Services\RegionSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateRegionsV2Command extends Command
{
    protected $signature = 'regions:migrate-v2';
    protected $description = 'Migrasi data region dari kode emsifa v1 (flat) ke v2 (titik), lalu sync ulang';

    /**
     * Mapping kode v1 (lama) -> v2 (baru) per level.
     * Hanya regency/district/village yang berubah; provinsi tetap sama.
     */
    private array $levelMap = [
        'regency' => [
            '1301' => '13.09',   // Kepulauan Mentawai
            '1101' => '11.09',   // Simeulue
            '5102' => '51.02',   // Tabanan
            '3301' => '33.01',   // Cilacap
        ],
        'district' => [
            '1301011' => '13.09.10',   // Pagai Selatan
            '1101010' => '11.09.07',   // Teupah Selatan
            '5102080' => '51.02.10',   // Pupuan
            '3301130' => '33.01.08',   // Jeruklegi
        ],
        'village' => [
            '1301011001' => '13.09.10.2002',   // Bulasat
            '1101010001' => '11.09.07.2008',   // Latiung
            '5102080013' => '51.02.10.2013',   // Karyasari
            '3301130012' => '33.01.08.2010',   // Prapagan
        ],
    ];

    public function handle(RegionSyncService $service): int
    {
        $this->info('1. Update kode region di data existing...');
        $this->migrateExistingRecords();

        $this->info('2. Hapus tabel regions lama...');
        // pakai delete() karena user DB production hanya punya privilege DELETE (tanpa DROP)
        Region::query()->delete();

        $this->info('3. Sync region v2 (emsifa)...');
        $service->syncAll(true);

        $this->info('Selesai.');
        $this->line('Provinsi: ' . Region::where('level', 'province')->count());
        $this->line('Kabupaten: ' . Region::where('level', 'regency')->count());
        $this->line('Kecamatan: ' . Region::where('level', 'district')->count());
        $this->line('Desa: ' . Region::where('level', 'village')->count());

        return 0;
    }

    private function migrateExistingRecords(): void
    {
        $tables = [
            'customers' => [
                'province_code' => 'province',
                'regency_code' => 'regency',
                'district_code' => 'district',
                'village_code' => 'village',
            ],
            'invoices' => [
                'customer_province_code' => 'province',
                'customer_regency_code' => 'regency',
                'customer_district_code' => 'district',
                'customer_village_code' => 'village',
            ],
            'shipments' => [
                'sender_province_code' => 'province',
                'sender_regency_code' => 'regency',
                'sender_district_code' => 'district',
                'sender_village_code' => 'village',
                'receiver_province_code' => 'province',
                'receiver_regency_code' => 'regency',
                'receiver_district_code' => 'district',
                'receiver_village_code' => 'village',
            ],
        ];

        foreach ($tables as $table => $cols) {
            $total = 0;
            foreach ($cols as $col => $level) {
                if (!isset($this->levelMap[$level])) {
                    continue;
                }
                foreach ($this->levelMap[$level] as $old => $new) {
                    // (string) penting: key array numerik PHP di-cast ke int,
                    // kalau tidak di-cast MySQL komparasi varchar vs angka -> error
                    $affected = DB::table($table)
                        ->where($col, (string) $old)
                        ->update([$col => $new]);
                    $total += $affected;
                }
            }
            $this->line("   {$table}: {$total} kolom diperbarui");
        }
    }
}
