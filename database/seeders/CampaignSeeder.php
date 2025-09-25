<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignCustomerSegment;
use App\Models\CampaignProductTarget;
use App\Models\CampaignSchedule;
use App\Models\CampaignTranslation;
use App\Models\CampaignView;
use Illuminate\Database\Seeder;

final class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = Campaign::query()
            ->with(['views', 'clicks', 'conversions', 'customerSegments', 'productTargets', 'schedules'])
            ->get();

        if ($campaigns->isEmpty()) {
            Campaign::factory()
                ->count(15)
                ->create();

            $campaigns = Campaign::query()->get();
        }

        $campaigns->each(function (Campaign $campaign): void {
            if (! $campaign->views()->exists()) {
                $campaign->views()->saveMany(
                    CampaignView::factory()->count(20)->make()
                );
            }

            if (! $campaign->clicks()->exists()) {
                $campaign->clicks()->saveMany(
                    CampaignClick::factory()->count(10)->make()
                );
            }

            if (! $campaign->conversions()->exists()) {
                $campaign->conversions()->saveMany(
                    CampaignConversion::factory()->count(5)->make()
                );
            }

            if (! $campaign->customerSegments()->exists()) {
                $campaign->customerSegments()->saveMany(
                    CampaignCustomerSegment::factory()->count(3)->make()
                );
            }

            if (! $campaign->productTargets()->exists()) {
                $campaign->productTargets()->saveMany(
                    CampaignProductTarget::factory()->count(4)->make()
                );
            }

            if (! $campaign->schedules()->exists()) {
                $campaign->schedules()->saveMany(
                    CampaignSchedule::factory()->count(2)->make()
                );
            }
        });

        // Create special seasonal campaigns
        $seasonalCampaigns = [
            [
                'name' => 'Summer Sale 2024',
                'slug' => 'summer-sale-2024',
                'status' => 'active',
                'is_featured' => true,
                'display_priority' => 10,
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(20),
                'banner_image' => 'summer-sale-banner.jpg',
                'cta_text' => 'Shop Summer Collection',
                'cta_url' => '/collections/summer',
                'meta_title' => 'Summer Sale 2024 - Up to 50% Off',
                'meta_description' => 'Discover amazing summer deals with up to 50% off on selected items. Limited time offer!',
                'target_audience' => [
                    'age_range' => '18-45',
                    'gender' => 'all',
                    'interests' => ['fashion', 'summer', 'outdoor'],
                ],
            ],
            [
                'name' => 'Black Friday 2024',
                'slug' => 'black-friday-2024',
                'status' => 'scheduled',
                'is_featured' => true,
                'display_priority' => 10,
                'starts_at' => now()->addDays(30),
                'ends_at' => now()->addDays(33),
                'banner_image' => 'black-friday-banner.jpg',
                'cta_text' => 'Get Early Access',
                'cta_url' => '/black-friday',
                'meta_title' => 'Black Friday 2024 - Biggest Sale of the Year',
                'meta_description' => "Don't miss our biggest sale of the year! Massive discounts on all products.",
                'target_audience' => [
                    'age_range' => 'all',
                    'gender' => 'all',
                    'interests' => ['shopping', 'deals', 'electronics'],
                ],
            ],
            [
                'name' => 'New Year Special',
                'slug' => 'new-year-special-2024',
                'status' => 'scheduled',
                'is_featured' => true,
                'display_priority' => 9,
                'starts_at' => now()->addDays(60),
                'ends_at' => now()->addDays(67),
                'banner_image' => 'new-year-banner.jpg',
                'cta_text' => 'Start Fresh',
                'cta_url' => '/new-year',
                'meta_title' => 'New Year Special - Fresh Start with New Products',
                'meta_description' => 'Start the new year with our special collection of fresh and innovative products.',
                'target_audience' => [
                    'age_range' => '25-55',
                    'gender' => 'all',
                    'interests' => ['lifestyle', 'wellness', 'home'],
                ],
            ],
        ];

        foreach ($seasonalCampaigns as $campaignData) {
            $translations = $campaignData['translations'] ?? [];
            unset($campaignData['translations']);

            $campaign = Campaign::factory()->create($campaignData);

            $this->syncTranslations($campaign, ['lt', 'en'], $translations);

            // Add analytics data for active campaigns
            if ($campaign->status === 'active') {
                $campaign->update([
                    'total_views' => fake()->numberBetween(1000, 10000),
                    'total_clicks' => fake()->numberBetween(100, 1000),
                    'total_conversions' => fake()->numberBetween(20, 200),
                    'total_revenue' => fake()->randomFloat(2, 2000, 20000),
                    'conversion_rate' => fake()->randomFloat(2, 2, 20),
                ]);
            }
        }

        $translationCount = CampaignTranslation::count();
        $this->command?->info(sprintf(
            'Created %d campaigns with full analytics, targeting data, and %d localized translations.',
            Campaign::query()->count(),
            $translationCount
        ));
    }

    private function syncTranslations(Campaign $campaign, array $locales, array $overrides = []): void
    {
        foreach ($locales as $locale) {
            $defaults = $this->translationDefaults($campaign, $locale);
            $data = array_merge($defaults, $overrides[$locale] ?? []);

            CampaignTranslation::updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'locale' => $locale,
                ],
                $data
            );
        }
    }

    private function translationDefaults(Campaign $campaign, string $locale): array
    {
        $fakerLocale = $this->fakerLocale($locale);
        $faker = FakerFactory::create($fakerLocale);

        $defaultLocale = config('app.locale', 'en');
        $isDefaultLocale = $locale === $defaultLocale;

        $baseName = $campaign->name ?? $faker->sentence(3);
        $baseDescription = $campaign->description ?? $faker->paragraph();
        $baseSubject = $campaign->metadata['subject'] ?? $campaign->metadata['email_subject'] ?? $campaign->name ?? $faker->sentence(4);
        $baseContent = $campaign->metadata['content'] ?? $faker->paragraphs(3, true);
        $baseMetaTitle = $campaign->metadata['meta_title'] ?? $campaign->name ?? $faker->sentence(3);
        $baseMetaDescription = $campaign->metadata['meta_description'] ?? $campaign->description ?? $faker->sentence(15);

        $name = $isDefaultLocale ? $baseName : $faker->sentence(3);
        $description = $isDefaultLocale ? $baseDescription : $faker->paragraph();
        $subject = $isDefaultLocale ? $baseSubject : $faker->sentence(4);
        $content = $isDefaultLocale ? $baseContent : $faker->paragraphs(3, true);
        $metaTitle = $isDefaultLocale ? $baseMetaTitle : sprintf('%s - %s', $faker->sentence(3), $faker->words(2, true));
        $metaDescription = $isDefaultLocale ? $baseMetaDescription : $faker->sentence(16);

        $slugBase = $campaign->slug ?? Str::slug($campaign->name ?? 'campaign-'.$campaign->id);
        $slug = $isDefaultLocale ? $slugBase : Str::slug($slugBase.'-'.$locale);

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'subject' => $subject,
            'content' => $content,
            'cta_text' => $this->localizedCallToAction($locale),
            'banner_alt_text' => $this->localizedBannerAltText($locale, $name),
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
        ];
    }

    private function localizedCallToAction(string $locale): string
    {
        return match ($locale) {
            'lt' => 'Peržiūrėti pasiūlymą',
            'en' => 'View the offer',
            'de' => 'Angebot ansehen',
            'ru' => 'Посмотреть предложение',
            default => 'View the offer',
        };
    }

    private function localizedBannerAltText(string $locale, string $campaignName): string
    {
        return match ($locale) {
            'lt' => $campaignName.' kampanijos baneris',
            'en' => $campaignName.' campaign banner',
            'de' => 'Kampagnenbanner '.$campaignName,
            'ru' => 'Баннер кампании '.$campaignName,
            default => $campaignName.' banner',
        };
    }

    private function fakerLocale(string $locale): string
    {
        return match ($locale) {
            'lt' => 'lt_LT',
            'en' => 'en_US',
            'de' => 'de_DE',
            'ru' => 'ru_RU',
            default => 'en_US',
        };
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt,en')))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function ensureRecords(string $modelClass, int $minimum): Collection
    {
        $ids = $modelClass::query()->pluck('id');

        if ($ids->count() >= $minimum) {
            return $ids;
        }

        $missing = max(0, $minimum - $ids->count());

        if ($missing > 0 && method_exists($modelClass, 'factory')) {
            try {
                $modelClass::factory()->count($missing)->create();

                return $modelClass::query()->pluck('id');
            } catch (\Throwable $exception) {
                // Swallow factory exceptions to keep seeding resilient
            }
        }

        return $modelClass::query()->pluck('id');
    }

    private function createCampaignClickSafely(array $attributes): void
    {
        if ($this->skipCampaignClickSeeding) {
            return;
        }

        try {
            CampaignClick::factory()->create($attributes);
        } catch (QueryException $exception) {
            if ($this->isMissingTableException($exception, 'campaign_clicks')) {
                $this->skipCampaignClickSeeding = true;
                $this->command?->warn('Skipping campaign click seeding: '.$exception->getMessage());

                return;
            }

            throw $exception;
        }
    }

    private function createCampaignConversionSafely(array $attributes): void
    {
        if ($this->skipCampaignConversionSeeding) {
            return;
        }

        try {
            CampaignConversion::factory()->create($attributes);
        } catch (QueryException $exception) {
            if ($this->isMissingTableException($exception, 'campaign_conversions')) {
                $this->skipCampaignConversionSeeding = true;
                $this->command?->warn('Skipping campaign conversion seeding: '.$exception->getMessage());

                return;
            }

            throw $exception;
        }
    }

    private function createCampaignProductTargetSafely(int $campaignId, ?int $productId, ?int $categoryId, string $targetType): void
    {
        if ($productId === null && $categoryId === null) {
            return;
        }

        try {
            CampaignProductTarget::query()->create([
                'campaign_id' => $campaignId,
                'product_id' => $productId,
                'category_id' => $categoryId,
                'target_type' => $targetType,
            ]);
        } catch (QueryException $exception) {
            if ($this->isUniqueConstraintException($exception, 'campaign_product_targets')) {
                return;
            }

            throw $exception;
        }
    }

    private function isMissingTableException(QueryException $exception, string $table): bool
    {
        if ($exception->getCode() === '42S02') {
            return true;
        }

        $database = Schema::getConnection()->getDatabaseName();

        return str_contains($exception->getMessage(), "Table '{$database}.{$table}' doesn't exist");
    }

    private function isUniqueConstraintException(QueryException $exception, string $table): bool
    {
        if ($exception->getCode() !== '23000') {
            return false;
        }

        return str_contains($exception->getMessage(), $table);
    }
}
