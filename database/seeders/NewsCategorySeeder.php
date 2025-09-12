<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Translations\NewsCategoryTranslation;
use Illuminate\Database\Seeder;

final class NewsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $locales = collect(config('app.supported_locales', 'lt,en'))
            ->when(fn ($v) => is_string($v), fn ($c) => collect(preg_split('/[\s,|]+/', (string) $v, -1, PREG_SPLIT_NO_EMPTY)))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values();

        $categories = [
            ['lt' => 'Naujienos', 'en' => 'News'],
            ['lt' => 'Projektai', 'en' => 'Projects'],
            ['lt' => 'Patarimai', 'en' => 'Tips'],
        ];

        $created = collect();
        foreach ($categories as $i => $names) {
            /** @var NewsCategory $cat */
            $cat = NewsCategory::query()->create([
                'is_visible' => true,
                'sort_order' => $i,
            ]);
            foreach ($locales as $locale) {
                $base = $names[$locale] ?? $names['en'] ?? 'Category';
                NewsCategoryTranslation::updateOrCreate([
                    'news_category_id' => $cat->id,
                    'locale' => $locale,
                ], [
                    'name' => $base,
                    'slug' => str($base)->slug()->toString(),
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                ]);
            }
            $created->push($cat);
        }

        // Attach existing news to categories in a round-robin fashion
        $news = News::query()->orderBy('id')->get();
        $catIds = $created->pluck('id')->all();
        $count = count($catIds);
        foreach ($news as $index => $item) {
            $item->categories()->syncWithoutDetaching([$catIds[$index % $count]]);
        }
    }
}
