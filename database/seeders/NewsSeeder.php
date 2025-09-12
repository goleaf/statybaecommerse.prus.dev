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
        $locales = collect(config('app.supported_locales', 'lt,en'))
            ->when(fn ($v) => is_string($v), fn ($c) => collect(preg_split('/[\s,|]+/', (string) $v, -1, PREG_SPLIT_NO_EMPTY)))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values();

        // Create 12 news items with different dates
        for ($i = 1; $i <= 12; $i++) {
            /** @var News $news */
            $news = News::factory()->create([
                'published_at' => now()->subDays($i),
            ]);

            foreach ($locales as $locale) {
                // Use Lorem Ipsum for all languages as requested
                $titleBase = "Demo News {$i}";
                $slugBase = str($titleBase)->slug()->toString();
                $summary = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
                $content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus imperdiet, nulla et dictum interdum, nisi lorem egestas odio.</p>';

                // Localize title minimally by appending locale code
                $title = $locale === 'lt' ? "Demonstracinė naujiena {$i}" : ($locale === 'en' ? $titleBase : $titleBase." ({$locale})");
                $slug = $locale === 'lt' ? str("Demonstracinė naujiena {$i}")->slug()->toString() : $slugBase.($locale === 'en' ? '' : "-{$locale}");

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
