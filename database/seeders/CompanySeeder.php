<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

final class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companiesData = [
            [
                'name' => 'Statybos Centras UAB',
                'email' => 'info@statyboscentras.lt',
                'phone' => '+370 5 234 5678',
                'address' => 'Gedimino pr. 1, Vilnius',
                'website' => 'https://statyboscentras.lt',
                'industry' => 'construction',
                'size' => 'large',
                'description' => 'Leading construction company in Lithuania specializing in residential and commercial projects.',
                'is_active' => true,
            ],
            [
                'name' => 'Lietuvos Statybos',
                'email' => 'contact@lietuvosstatybos.lt',
                'phone' => '+370 37 123 4567',
                'address' => 'Laisvės al. 15, Kaunas',
                'website' => 'https://lietuvosstatybos.lt',
                'industry' => 'construction',
                'size' => 'medium',
                'description' => 'Modern construction company focused on sustainable building solutions.',
                'is_active' => true,
            ],
            [
                'name' => 'Vilniaus Statybos',
                'email' => 'info@vilniausstatybos.lt',
                'phone' => '+370 5 345 6789',
                'address' => 'Vilniaus g. 25, Vilnius',
                'website' => 'https://vilniausstatybos.lt',
                'industry' => 'construction',
                'size' => 'medium',
                'description' => 'Regional construction company serving Vilnius and surrounding areas.',
                'is_active' => true,
            ],
            [
                'name' => 'Kauno Statybos',
                'email' => 'info@kaunostatybos.lt',
                'phone' => '+370 37 234 5678',
                'address' => 'Kauno g. 10, Kaunas',
                'website' => 'https://kaunostatybos.lt',
                'industry' => 'construction',
                'size' => 'medium',
                'description' => 'Professional construction services in Kaunas region.',
                'is_active' => true,
            ],
            [
                'name' => 'Klaipėdos Statybos',
                'email' => 'contact@klaipedostatybos.lt',
                'phone' => '+370 46 123 4567',
                'address' => 'Tiltų g. 5, Klaipėda',
                'website' => 'https://klaipedostatybos.lt',
                'industry' => 'construction',
                'size' => 'small',
                'description' => 'Local construction company specializing in residential projects.',
                'is_active' => true,
            ],
            [
                'name' => 'Panevėžio Statybos',
                'email' => 'info@paneveziostatybos.lt',
                'phone' => '+370 45 234 5678',
                'address' => 'Respublikos g. 20, Panevėžys',
                'website' => 'https://paneveziostatybos.lt',
                'industry' => 'construction',
                'size' => 'small',
                'description' => 'Family-owned construction business with 20+ years of experience.',
                'is_active' => true,
            ],
            [
                'name' => 'Šiaulių Statybos',
                'email' => 'contact@siauliustatybos.lt',
                'phone' => '+370 41 345 6789',
                'address' => 'Vilniaus g. 30, Šiauliai',
                'website' => 'https://siauliustatybos.lt',
                'industry' => 'construction',
                'size' => 'small',
                'description' => 'Regional construction company focused on quality and reliability.',
                'is_active' => true,
            ],
            [
                'name' => 'Alytaus Statybos',
                'email' => 'info@alytausstatybos.lt',
                'phone' => '+370 315 12345',
                'address' => 'Dainavos g. 12, Alytus',
                'website' => 'https://alytausstatybos.lt',
                'industry' => 'construction',
                'size' => 'small',
                'description' => 'Local construction services with emphasis on traditional building methods.',
                'is_active' => true,
            ],
            [
                'name' => 'Marijampolės Statybos',
                'email' => 'contact@marijampolestatybos.lt',
                'phone' => '+370 343 23456',
                'address' => 'Vilniaus g. 8, Marijampolė',
                'website' => 'https://marijampolestatybos.lt',
                'industry' => 'construction',
                'size' => 'small',
                'description' => 'Professional construction company serving Marijampolė region.',
                'is_active' => true,
            ],
            [
                'name' => 'Tauragės Statybos',
                'email' => 'info@tauragetatybos.lt',
                'phone' => '+370 446 34567',
                'address' => 'Vytauto g. 15, Tauragė',
                'website' => 'https://tauragetatybos.lt',
                'industry' => 'construction',
                'size' => 'small',
                'description' => 'Established construction company with focus on residential development.',
                'is_active' => true,
            ],
        ];

        // Create companies using factory with specific data
        collect($companiesData)->each(function (array $companyData): void {
            Company::factory()
                ->state($companyData)
                ->create();
        });

        $this->command->info('Created ' . count($companiesData) . ' construction companies using factories');
    }
}
