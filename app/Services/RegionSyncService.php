<?php
namespace App\Services;

use App\Models\Region;
use Illuminate\Support\Facades\Http;

class RegionSyncService
{
    private const BASE_URL = 'https://www.emsifa.com/api-wilayah-indonesia/v2';

    public function syncAll(bool $force = false): void
    {
        if ($force || Region::where('level', 'province')->count() < 30) {
            $this->syncProvinces();
        }
        if ($force || Region::where('level', 'regency')->count() < 500) {
            $this->syncRegencies();
        }
        if ($force || Region::where('level', 'district')->count() < 7000) {
            $this->syncDistricts();
        }
        $this->syncVillages($force);
    }

    private function syncProvinces(): void
    {
        $response = Http::get(self::BASE_URL . '/provinces.json');
        foreach ($response->json() as $item) {
            Region::updateOrCreate(
                ['code' => $item['id']],
                ['name' => $item['name'], 'level' => 'province', 'parent_code' => null]
            );
        }
    }

    private function syncRegencies(): void
    {
        $provinces = Region::where('level', 'province')->get();
        foreach ($provinces as $province) {
            $response = Http::get(self::BASE_URL . "/regencies/{$province->code}.json");
            $data = $response->json();
            if (!is_array($data)) continue;
            foreach ($data as $item) {
                Region::updateOrCreate(
                    ['code' => $item['id']],
                    ['name' => $item['name'], 'level' => 'regency', 'parent_code' => $province->code]
                );
            }
        }
    }

    private function syncDistricts(): void
    {
        $regencies = Region::where('level', 'regency')->get();
        foreach ($regencies as $regency) {
            $response = Http::get(self::BASE_URL . "/districts/{$regency->code}.json");
            $data = $response->json();
            if (!is_array($data)) continue;
            foreach ($data as $item) {
                Region::updateOrCreate(
                    ['code' => $item['id']],
                    ['name' => $item['name'], 'level' => 'district', 'parent_code' => $regency->code]
                );
            }
        }
    }

    private function syncVillages(bool $force = false): void
    {
        $districts = Region::where('level', 'district')->get();
        foreach ($districts as $district) {
            // Skip if already synced (has villages) unless force
            if (!$force && Region::where('level', 'village')->where('parent_code', $district->code)->exists()) {
                continue;
            }
            try {
                $response = Http::timeout(30)->get(self::BASE_URL . "/villages/{$district->code}.json");
                $data = $response->json();
                if (!is_array($data)) continue;
                foreach ($data as $item) {
                    Region::updateOrCreate(
                        ['code' => $item['id']],
                        ['name' => $item['name'], 'level' => 'village', 'parent_code' => $district->code]
                    );
                }
            } catch (\Exception $e) {
                // Skip failed districts, continue with next
                continue;
            }
        }
    }
}
