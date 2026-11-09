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

    private function row(string $code, string $name, string $level, ?string $parent): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'level' => $level,
            'parent_code' => $parent,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function syncProvinces(): void
    {
        $response = Http::get(self::BASE_URL . '/provinces.json');
        $data = $response->json('data', []);
        $rows = [];
        foreach ($data as $item) {
            $rows[] = $this->row($item['id'], $item['name'], 'province', null);
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            Region::upsert($chunk, ['code'], ['name', 'parent_code']);
        }
    }

    private function syncRegencies(): void
    {
        $provinces = Region::where('level', 'province')->get();
        foreach ($provinces as $province) {
            try {
                $response = Http::timeout(30)->get(self::BASE_URL . "/regencies/{$province->code}.json");
                $data = $response->json('data', []);
                if (!is_array($data) || empty($data)) continue;
                $rows = [];
                foreach ($data as $item) {
                    $rows[] = $this->row($item['id'], $item['name'], 'regency', $province->code);
                }
                Region::upsert($rows, ['code'], ['name', 'parent_code']);
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    private function syncDistricts(): void
    {
        $regencies = Region::where('level', 'regency')->get();
        foreach ($regencies as $regency) {
            try {
                $response = Http::timeout(30)->get(self::BASE_URL . "/districts/{$regency->code}.json");
                $data = $response->json('data', []);
                if (!is_array($data) || empty($data)) continue;
                $rows = [];
                foreach ($data as $item) {
                    $rows[] = $this->row($item['id'], $item['name'], 'district', $regency->code);
                }
                Region::upsert($rows, ['code'], ['name', 'parent_code']);
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    private function syncVillages(bool $force = false): void
    {
        $districts = Region::where('level', 'district')->get();

        // Skip district yang sudah punya village (kecuali force)
        if (!$force) {
            $synced = Region::where('level', 'village')->distinct()->pluck('parent_code');
            $districts = $districts->reject(fn($d) => $synced->contains($d->code));
        }

        $total = $districts->count();
        $processed = 0;

        // Concurrency fetch: 30 request paralel per batch
        $districts->chunk(30)->each(function ($chunk) use (&$processed, $total) {
            $chunk = $chunk->values();
            $responses = Http::pool(function ($pool) use ($chunk) {
                foreach ($chunk as $district) {
                    $pool->get(self::BASE_URL . "/villages/{$district->code}.json");
                }
            });

            $rows = [];
            foreach ($responses as $i => $response) {
                try {
                    if (!($response instanceof \Illuminate\Http\Client\Response) || !$response->successful()) {
                        continue;
                    }
                    $data = $response->json('data', []);
                    if (!is_array($data)) continue;
                    $district = $chunk[$i];
                    foreach ($data as $item) {
                        $rows[] = $this->row($item['id'], $item['name'], 'village', $district->code);
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }

            foreach (array_chunk($rows, 500) as $batch) {
                Region::upsert($batch, ['code'], ['name', 'parent_code']);
            }

            $processed += $chunk->count();
            if ($processed % 1000 < 30) {
                echo "   progress: {$processed}/{$total} districts" . PHP_EOL;
            }
        });
    }
}
