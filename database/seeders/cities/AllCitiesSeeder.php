<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use Illuminate\Database\Seeder;

final class AllCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting cities seeding process...');

        // Seed cities for each country
        $seeders = [
            'Lithuania' => LithuaniaCitiesSeeder::class,
            'Latvia' => LatviaCitiesSeeder::class,
            'Estonia' => EstoniaCitiesSeeder::class,
            'Poland' => PolandCitiesSeeder::class,
            'Germany' => GermanyCitiesSeeder::class,
            'France' => FranceCitiesSeeder::class,
            'United Kingdom' => UKCitiesSeeder::class,
            'United States' => USACitiesSeeder::class,
            'Spain' => SpainCitiesSeeder::class,
            'Italy' => ItalyCitiesSeeder::class,
            'Russia' => RussiaCitiesSeeder::class,
            'Canada' => CanadaCitiesSeeder::class,
            'Netherlands' => NetherlandsCitiesSeeder::class,
            'Belgium' => BelgiumCitiesSeeder::class,
            'Sweden' => SwedenCitiesSeeder::class,
            'Norway' => NorwayCitiesSeeder::class,
            'Denmark' => DenmarkCitiesSeeder::class,
            'Finland' => FinlandCitiesSeeder::class,
            'Austria' => AustriaCitiesSeeder::class,
            'Switzerland' => SwitzerlandCitiesSeeder::class,
            'Czech Republic' => CzechRepublicCitiesSeeder::class,
            'Slovakia' => SlovakiaCitiesSeeder::class,
            'Hungary' => HungaryCitiesSeeder::class,
            'Romania' => RomaniaCitiesSeeder::class,
            'Bulgaria' => BulgariaCitiesSeeder::class,
            'Croatia' => CroatiaCitiesSeeder::class,
            'Slovenia' => SloveniaCitiesSeeder::class,
            'Serbia' => SerbiaCitiesSeeder::class,
            'Ukraine' => UkraineCitiesSeeder::class,
            'Belarus' => BelarusCitiesSeeder::class,
            'Australia' => AustraliaCitiesSeeder::class,
            'Japan' => JapanCitiesSeeder::class,
            'China' => ChinaCitiesSeeder::class,
        ];

        foreach ($seeders as $country => $seederClass) {
            $this->command->info("Seeding cities for {$country}...");

            try {
                $this->call($seederClass);
                $this->command->info("✓ Successfully seeded cities for {$country}");
            } catch (\Exception $e) {
                $this->command->error("✗ Failed to seed cities for {$country}: ".$e->getMessage());
            }
        }

        $this->command->info('Cities seeding process completed!');
    }
}
