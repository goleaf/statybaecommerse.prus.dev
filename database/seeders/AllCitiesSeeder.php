<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class AllCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting cities seeding process...');

        // Seed cities for each country
        $seeders = [
            'Lithuania' => \Database\Seeders\Cities\LithuaniaCitiesSeeder::class,
            'Latvia' => \Database\Seeders\Cities\LatviaCitiesSeeder::class,
            'Estonia' => \Database\Seeders\Cities\EstoniaCitiesSeeder::class,
            'Poland' => \Database\Seeders\Cities\PolandCitiesSeeder::class,
            'Germany' => \Database\Seeders\Cities\GermanyCitiesSeeder::class,
            'France' => \Database\Seeders\Cities\FranceCitiesSeeder::class,
            'United Kingdom' => \Database\Seeders\Cities\UKCitiesSeeder::class,
            'United States' => \Database\Seeders\Cities\USACitiesSeeder::class,
            'Spain' => \Database\Seeders\Cities\SpainCitiesSeeder::class,
            'Italy' => \Database\Seeders\Cities\ItalyCitiesSeeder::class,
            'Russia' => \Database\Seeders\Cities\RussiaCitiesSeeder::class,
            'Canada' => \Database\Seeders\Cities\CanadaCitiesSeeder::class,
            'Netherlands' => \Database\Seeders\Cities\NetherlandsCitiesSeeder::class,
            'Belgium' => \Database\Seeders\Cities\BelgiumCitiesSeeder::class,
            'Sweden' => \Database\Seeders\Cities\SwedenCitiesSeeder::class,
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
