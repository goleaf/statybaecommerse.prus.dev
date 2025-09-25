<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UiTranslation;
use Illuminate\Database\Seeder;

final class NewsTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'lt' => [
                'news.fields.title' => 'Pavadinimas',
                'news.fields.slug' => 'URL slug',
                'news.fields.excerpt' => 'Santrauka',
                'news.fields.content' => 'Turinys',
                'news.fields.published_at' => 'Paskelbimo data',
                'news.fields.author_name' => 'Autoriaus vardas',
                'news.fields.author_email' => 'Autoriaus el. paštas',
                'news.fields.is_visible' => 'Ar matoma',
                'news.fields.is_featured' => 'Ar rekomenduojama',
                'news.fields.featured_image' => 'Pagrindinis paveikslėlis',
                'news.fields.categories' => 'Kategorijos',
                'news.fields.tags' => 'Žymės',
                'news.fields.meta_title' => 'Meta pavadinimas',
                'news.fields.meta_description' => 'Meta aprašymas',
                'news.fields.meta_keywords' => 'Meta raktažodžiai',
                'news.fields.created_at' => 'Sukurta',
                'news.fields.view_count' => 'Peržiūrų skaičius',
                'news.filters.published_from' => 'Paskelbta nuo',
                'news.filters.published_until' => 'Paskelbta iki',
            ],
            'en' => [
                'news.fields.title' => 'Title',
                'news.fields.slug' => 'URL slug',
                'news.fields.excerpt' => 'Excerpt',
                'news.fields.content' => 'Content',
                'news.fields.published_at' => 'Published At',
                'news.fields.author_name' => 'Author Name',
                'news.fields.author_email' => 'Author Email',
                'news.fields.is_visible' => 'Is Visible',
                'news.fields.is_featured' => 'Is Featured',
                'news.fields.featured_image' => 'Featured Image',
                'news.fields.categories' => 'Categories',
                'news.fields.tags' => 'Tags',
                'news.fields.meta_title' => 'Meta Title',
                'news.fields.meta_description' => 'Meta Description',
                'news.fields.meta_keywords' => 'Meta Keywords',
                'news.fields.created_at' => 'Created At',
                'news.fields.view_count' => 'View Count',
                'news.filters.published_from' => 'Published From',
                'news.filters.published_until' => 'Published Until',
            ],
        ];

        // Store translations in database using UiTranslation model
        foreach ($translations as $locale => $localeTranslations) {
            foreach ($localeTranslations as $key => $value) {
                UiTranslation::factory()
                    ->forKey($key)
                    ->forLocale($locale)
                    ->forGroup('news')
                    ->create([
                        'value' => $value,
                        'metadata' => [
                            'context' => 'news_admin_interface',
                            'description' => "News admin interface translation for {$key}",
                            'seeded_at' => now()->toISOString(),
                        ],
                    ]);
            }
        }
    }
}
