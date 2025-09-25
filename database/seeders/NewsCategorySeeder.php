<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Translations\NewsCategoryTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class NewsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $supportedLocales = config('app.supported_locales', 'lt,en');
        $locales = collect(is_array($supportedLocales)
            ? $supportedLocales
            : array_filter(array_map('trim', explode(',', (string) $supportedLocales))))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values();

        $definitions = [
            [
                'sort_order' => 0,
                'attributes' => ['color' => '#1e40af', 'icon' => 'heroicon-o-newspaper'],
                'translations' => [
                    'lt' => ['name' => 'Naujienos', 'description' => 'Pagrindinių įmonės naujienų apžvalga.'],
                    'en' => ['name' => 'News', 'description' => 'Overview of the latest company news.'],
                ],
            ],
            [
                'sort_order' => 1,
                'attributes' => ['color' => '#047857', 'icon' => 'heroicon-o-rectangle-stack'],
                'translations' => [
                    'lt' => ['name' => 'Projektai', 'description' => 'Vykdomų ir baigtų projektų pristatymas.'],
                    'en' => ['name' => 'Projects', 'description' => 'Highlights of ongoing and finished projects.'],
                ],
            ],
            [
                'sort_order' => 2,
                'attributes' => ['color' => '#c2410c', 'icon' => 'heroicon-o-light-bulb'],
                'translations' => [
                    'lt' => ['name' => 'Patarimai', 'description' => 'Praktiniai patarimai ir rekomendacijos klientams.'],
                    'en' => ['name' => 'Tips', 'description' => 'Practical tips and recommendations for customers.'],
                ],
            ],
        ];

        $slugs = collect($definitions)
            ->map(fn (array $definition) => Str::slug($definition['translations']['lt']['name']))
            ->all();

        NewsCategory::query()
            ->whereHas('translations', fn ($query) => $query->where('locale', 'lt')->whereIn('slug', $slugs))
            ->get()
            ->each(function (NewsCategory $category): void {
                $category->news()->detach();
                $category->delete();
            });

        $created = collect();
        foreach ($definitions as $definition) {
            $category = NewsCategory::factory()
                ->visible()
                ->create(array_merge(['sort_order' => $definition['sort_order']], $definition['attributes']));

            foreach ($locales as $locale) {
                $translation = $definition['translations'][$locale] ?? $definition['translations']['en'];

                NewsCategoryTranslation::factory()
                    ->state([
                        'news_category_id' => $category->id,
                        'locale' => $locale,
                        'name' => $translation['name'],
                        'slug' => Str::slug($translation['name']),
                        'description' => $translation['description'],
                    ])
                    ->create();
            }

            $created->push($category);
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
