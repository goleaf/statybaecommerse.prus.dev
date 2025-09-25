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
        $variants = ProductVariant::factory()->count(10)->create();

        $this->command->info('Creating variant analytics data...');

        $variants->each(function (ProductVariant $variant): void {
            VariantAnalytics::factory()
                ->count(30)
                ->withVariant($variant)
                ->create();
        });

        $variants->take(5)->each(function (ProductVariant $variant): void {
            VariantAnalytics::factory()
                ->count(7)
                ->highPerforming()
                ->withVariant($variant)
                ->create();
        });

        $variants->skip(5)->take(3)->each(function (ProductVariant $variant): void {
            VariantAnalytics::factory()
                ->count(14)
                ->lowPerforming()
                ->withVariant($variant)
                ->create();
        });

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
