<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsImage;
use App\Models\Translations\NewsCategoryTranslation;
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

        // Create categories first
        $categories = $this->createCategories($locales);

        // Create 20 news items with different dates and relationships
        for ($i = 1; $i <= 20; $i++) {
            $news = News::factory()->create([
                'is_visible'   => fake()->boolean(80),  // 80% chance of being visible
                'is_featured'  => fake()->boolean(20),  // 20% chance of being featured
                'is_breaking'  => fake()->boolean(10),  // 10% chance of being breaking news
                'published_at' => fake()->boolean(70) ? now()->subDays(fake()->numberBetween(0, 30)) : null,
                'view_count'   => fake()->numberBetween(0, 1000),
            ]);

            // Create translations for each locale
            foreach ($locales as $locale) {
                $titleBase = "Demo News {$i}";
                $slugBase = str($titleBase)->slug()->toString();
                $summary = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
                $content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus imperdiet, nulla et dictum interdum, nisi lorem egestas odio.</p><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>';

                $title = $locale === 'lt' ? "Demonstracinė naujiena {$i}" : ($locale === 'en' ? $titleBase : $titleBase . " ({$locale})");
                $slug = $locale === 'lt' ? str("Demonstracinė naujiena {$i}")->slug()->toString() : $slugBase . ($locale === 'en' ? '' : "-{$locale}");
                $slug = $slug . "-{$news->id}";

                NewsTranslation::updateOrCreate([
                    'news_id' => $news->id,
                    'locale'  => $locale,
                ], [
                    'title'           => $title,
                    'slug'            => $slug,
                    'summary'         => $summary,
                    'content'         => $content,
                    'seo_title'       => $title,
                    'seo_description' => $summary,
                ]);
            }

            // Attach random categories (1-3 per news)
            $randomCategories = $categories->random(fake()->numberBetween(1, 3));
            $news->categories()->attach($randomCategories->pluck('id'));

            // Create images (0-3 per news)
            $imageCount = fake()->numberBetween(0, 3);
            for ($k = 0; $k < $imageCount; $k++) {
                NewsImage::factory()->create([
                    'news_id'     => $news->id,
                    'is_featured' => $k === 0 && fake()->boolean(30),  // 30% chance first image is featured
                ]);
            }
        }
    }

    private function createCategories($locales)
    {
        $categoryNames = [
            'Technology', 'Business', 'Sports', 'Entertainment', 'Health', 'Science',
            'Politics', 'Education', 'Travel', 'Food', 'Fashion', 'Automotive',
        ];

        $categories = collect();

        foreach ($categoryNames as $index => $name) {
            $category = NewsCategory::factory()->create([
                'is_visible' => true,
                'sort_order' => $index + 1,
                'color'      => fake()->hexColor(),
            ]);

            foreach ($locales as $locale) {
                $localizedName = $locale === 'lt'
                    ? ['Technologijos', 'Verslas', 'Sportas', 'Pramogos', 'Sveikata', 'Mokslas',
                        'Politika', 'Švietimas', 'Kelionės', 'Maistas', 'Mada', 'Automobiliai'][$index]
                    : $name;

                $slug = str($localizedName)->slug()->toString() . '-' . $category->id;

                NewsCategoryTranslation::updateOrCreate([
                    'news_category_id' => $category->id,
                    'locale'           => $locale,
                ], [
                    'name'        => $localizedName,
                    'slug'        => $slug,
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                ]);
            }

            $categories->push($category);
        }

        return $categories;
    }
}
