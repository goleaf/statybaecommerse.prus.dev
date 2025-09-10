<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => ['lt' => 'Pagrindinis sandėlis', 'en' => 'Main Warehouse'],
                'slug' => ['lt' => 'pagrindinis-sandelys', 'en' => 'main-warehouse'],
                'description' => ['lt' => 'Pagrindinė prekių saugojimo vieta', 'en' => 'Primary storage location'],
                'code' => 'WH-001',
                'address_line_1' => 'Sandėlio g. 1',
                'city' => 'Vilnius',
                'postal_code' => '01100',
                'country_code' => 'LT',
                'phone' => '+37060000001',
                'email' => 'warehouse@shop.lt',
                'is_enabled' => true,
                'is_default' => true,
                'type' => 'warehouse',
            ],
            [
                'name' => ['lt' => 'Atsarginis sandėlis', 'en' => 'Backup Warehouse'],
                'slug' => ['lt' => 'atsarginis-sandelys', 'en' => 'backup-warehouse'],
                'description' => ['lt' => 'Papildoma atsargų vieta', 'en' => 'Secondary stock location'],
                'code' => 'WH-002',
                'address_line_1' => 'Pramonės g. 10',
                'city' => 'Kaunas',
                'postal_code' => '44100',
                'country_code' => 'LT',
                'phone' => '+37060000002',
                'email' => 'backup@shop.lt',
                'is_enabled' => true,
                'is_default' => false,
                'type' => 'warehouse',
            ],
        ];

        foreach ($locations as $data) {
            Location::updateOrCreate(['code' => $data['code']], $data);
        }

        $this->command?->info('LocationSeeder: seeded default locations.');
    }
}


