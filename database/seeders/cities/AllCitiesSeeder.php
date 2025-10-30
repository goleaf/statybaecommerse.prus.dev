<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use Exception;
use Illuminate\Database\Seeder;

final class AllCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting cities seeding process...');

        // Seed cities for the subset of European countries that ship with curated datasets.
        $seeders = [
            'Austria'        => AustriaCitiesSeeder::class,
            'Belgium'        => BelgiumCitiesSeeder::class,
            'Bulgaria'       => BulgariaCitiesSeeder::class,
            'Croatia'        => CroatiaCitiesSeeder::class,
            'Czech Republic' => CzechRepublicCitiesSeeder::class,
            'Denmark'        => DenmarkCitiesSeeder::class,
            'Estonia'        => EstoniaCitiesSeeder::class,
            'Finland'        => FinlandCitiesSeeder::class,
            'France'         => FranceCitiesSeeder::class,
            'Germany'        => GermanyCitiesSeeder::class,
            'Hungary'        => HungaryCitiesSeeder::class,
            'Italy'          => ItalyCitiesSeeder::class,
            'Latvia'         => LatviaCitiesSeeder::class,
            'Lithuania'      => LithuaniaCitiesSeeder::class,
            'Netherlands'    => NetherlandsCitiesSeeder::class,
            'Norway'         => NorwayCitiesSeeder::class,
            'Poland'         => PolandCitiesSeeder::class,
            'Romania'        => RomaniaCitiesSeeder::class,
            'Serbia'         => SerbiaCitiesSeeder::class,
            'Slovakia'       => SlovakiaCitiesSeeder::class,
            'Slovenia'       => SloveniaCitiesSeeder::class,
            'Spain'          => SpainCitiesSeeder::class,
            'Sweden'         => SwedenCitiesSeeder::class,
            'Switzerland'    => SwitzerlandCitiesSeeder::class,
            'Turkey'         => TurkeyCitiesSeeder::class,
            'Ukraine'        => UkraineCitiesSeeder::class,
            'United Kingdom' => UKCitiesSeeder::class,
        ];

        foreach ($seeders as $country => $seederClass) {
            $this->command->info("Seeding cities for {$country}...");

            try {
                $this->call($seederClass);
                $this->command->info("✓ Successfully seeded cities for {$country}");
            } catch (Exception $e) {
                $this->command->error("✗ Failed to seed cities for {$country}: " . $e->getMessage());
            }
        }

        $this->command->info('Cities seeding process completed!');
    }
}
