<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\VariantAnalytics;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class VariantAnalyticsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Get all existing variants
        $variants = ProductVariant::all();

        if ($variants->isEmpty()) {
            $this->command->warn('No product variants found. Creating analytics for sample variants...');

            return;
        }

        $this->command->info('Creating variant analytics data...');

        // Create analytics for the last 30 days for each variant
        foreach ($variants as $variant) {
            $this->createAnalyticsForVariant($variant);
        }

        // Create some high-performing analytics
        $this->createHighPerformingAnalytics($variants->take(5));

        // Create some low-performing analytics
        $this->createLowPerformingAnalytics($variants->skip(5)->take(3));

        $this->command->info('Variant analytics data created successfully!');
    }

    private function createAnalyticsForVariant(ProductVariant $variant): void
    {
        $daysToCreate = 30;

        for ($i = 0; $i < $daysToCreate; $i++) {
            $date = now()->subDays($i);

            // Skip some days randomly (not all variants have analytics every day)
            if (fake()->boolean(20)) {
                continue;
            }

            VariantAnalytics::factory()
                ->withVariant($variant)
                ->forDate($date->toDateString())
                ->create();
        }
    }

    private function createHighPerformingAnalytics($variants): void
    {
        $this->command->info('Creating high-performing analytics...');

        foreach ($variants as $variant) {
            // Create high-performing analytics for the last 7 days
            for ($i = 0; $i < 7; $i++) {
                $date = now()->subDays($i);

                VariantAnalytics::factory()
                    ->highPerforming()
                    ->withVariant($variant)
                    ->forDate($date->toDateString())
                    ->create();
            }
        }
    }

    private function createLowPerformingAnalytics($variants): void
    {
        $this->command->info('Creating low-performing analytics...');

        foreach ($variants as $variant) {
            // Create low-performing analytics for the last 14 days
            for ($i = 0; $i < 14; $i++) {
                $date = now()->subDays($i);

                VariantAnalytics::factory()
                    ->lowPerforming()
                    ->withVariant($variant)
                    ->forDate($date->toDateString())
                    ->create();
            }
        }
    }
}
