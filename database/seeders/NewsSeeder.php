<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\News;
use App\Models\Translations\NewsTranslation;
use Illuminate\Database\Seeder;

final class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $supportedLocales = config('app.supported_locales', 'lt,en');
        $locales = collect(explode(',', (string) $supportedLocales))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values();

        // Create 12 news items with different dates
        for ($i = 1; $i <= 12; $i++) {
            // Check if news item already exists
            $news = News::where('published_at', now()->subDays($i)->toDateString())->first();
            
            if (!$news) {
                /** @var News $news */
                $news = News::factory()->create([
                    'published_at' => now()->subDays($i),
                ]);
            }

            foreach ($locales as $locale) {
                // Use Lorem Ipsum for all languages as requested
                $titleBase = "Demo News {$i}";
                $slugBase = str($titleBase)->slug()->toString();
                $summary = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
                $content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus imperdiet, nulla et dictum interdum, nisi lorem egestas odio.</p>';

                // Localize title minimally by appending locale code
                $title = $locale === 'lt' ? "Demonstracinė naujiena {$i}" : ($locale === 'en' ? $titleBase : $titleBase." ({$locale})");
                // Make slug unique by including news ID to avoid conflicts
                $slug = $locale === 'lt' ? str("Demonstracinė naujiena {$i}")->slug()->toString() : $slugBase.($locale === 'en' ? '' : "-{$locale}");
                $slug = $slug . "-{$news->id}"; // Add news ID to make slug unique

                NewsTranslation::updateOrCreate([
                    'news_id' => $news->id,
                    'locale' => $locale,
                ], [
                    'title' => $title,
                    'slug' => $slug,
                    'summary' => $summary,
                    'content' => $content,
                    'seo_title' => $title,
                    'seo_description' => $summary,
                ]);
            }
        }
    }
}
