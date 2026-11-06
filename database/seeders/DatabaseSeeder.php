<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use App\Models\Region;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
