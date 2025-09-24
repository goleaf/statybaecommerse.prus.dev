<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NewsTag;
use App\Models\Translations\NewsTagTranslation;
use Illuminate\Database\Seeder;

final class NewsTagSeeder extends Seeder
{
    public function run(): void
    {
        $supportedLocales = config('app.supported_locales', 'lt,en');
        $locales = collect(explode(',', (string) $supportedLocales))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values();

        $tagData = [
            [
                'name' => 'Breaking',
                'lt_name' => 'Skubūs',
                'description' => 'Urgent and breaking news',
                'lt_description' => 'Skubūs ir aktualūs įvykiai',
                'color' => '#EF4444',
                'sort_order' => 1,
            ],
            [
                'name' => 'Exclusive',
                'lt_name' => 'Ekskluzyvūs',
                'description' => 'Exclusive content and reports',
                'lt_description' => 'Ekskluzyvūs turinys ir ataskaitos',
                'color' => '#8B5CF6',
                'sort_order' => 2,
            ],
            [
                'name' => 'Trending',
                'lt_name' => 'Populiarūs',
                'description' => 'Currently trending topics',
                'lt_description' => 'Šiuo metu populiarūs temos',
                'color' => '#F59E0B',
                'sort_order' => 3,
            ],
            [
                'name' => 'Popular',
                'lt_name' => 'Populiarūs',
                'description' => 'Popular and well-liked content',
                'lt_description' => 'Populiarus ir mėgstamas turinys',
                'color' => '#10B981',
                'sort_order' => 4,
            ],
            [
                'name' => 'Latest',
                'lt_name' => 'Naujausi',
                'description' => 'Most recent updates',
                'lt_description' => 'Naujausi atnaujinimai',
                'color' => '#3B82F6',
                'sort_order' => 5,
            ],
            [
                'name' => 'Important',
                'lt_name' => 'Svarbūs',
                'description' => 'Important announcements',
                'lt_description' => 'Svarbūs pranešimai',
                'color' => '#DC2626',
                'sort_order' => 6,
            ],
            [
                'name' => 'Update',
                'lt_name' => 'Atnaujinimai',
                'description' => 'System and feature updates',
                'lt_description' => 'Sistemos ir funkcijų atnaujinimai',
                'color' => '#06B6D4',
                'sort_order' => 7,
            ],
            [
                'name' => 'Announcement',
                'lt_name' => 'Pranešimai',
                'description' => 'Official announcements',
                'lt_description' => 'Oficialūs pranešimai',
                'color' => '#8B5CF6',
                'sort_order' => 8,
            ],
            [
                'name' => 'Event',
                'lt_name' => 'Renginiai',
                'description' => 'Upcoming events and activities',
                'lt_description' => 'Artėjantys renginiai ir veiklos',
                'color' => '#EC4899',
                'sort_order' => 9,
            ],
            [
                'name' => 'News',
                'lt_name' => 'Naujienos',
                'description' => 'General news and information',
                'lt_description' => 'Bendros naujienos ir informacija',
                'color' => '#6366F1',
                'sort_order' => 10,
            ],
            [
                'name' => 'Report',
                'lt_name' => 'Ataskaitos',
                'description' => 'Detailed reports and analysis',
                'lt_description' => 'Detalios ataskaitos ir analizės',
                'color' => '#059669',
                'sort_order' => 11,
            ],
            [
                'name' => 'Analysis',
                'lt_name' => 'Analizės',
                'description' => 'In-depth analysis and insights',
                'lt_description' => 'Išsamios analizės ir įžvalgos',
                'color' => '#7C3AED',
                'sort_order' => 12,
            ],
            [
                'name' => 'Technology',
                'lt_name' => 'Technologijos',
                'description' => 'Technology-related content',
                'lt_description' => 'Technologijų turinys',
                'color' => '#0EA5E9',
                'sort_order' => 13,
            ],
            [
                'name' => 'Business',
                'lt_name' => 'Verslas',
                'description' => 'Business and economy news',
                'lt_description' => 'Verslo ir ekonomikos naujienos',
                'color' => '#059669',
                'sort_order' => 14,
            ],
            [
                'name' => 'Sports',
                'lt_name' => 'Sportas',
                'description' => 'Sports news and updates',
                'lt_description' => 'Sporto naujienos ir atnaujinimai',
                'color' => '#DC2626',
                'sort_order' => 15,
            ],
            [
                'name' => 'Entertainment',
                'lt_name' => 'Pramogos',
                'description' => 'Entertainment and culture',
                'lt_description' => 'Pramogos ir kultūra',
                'color' => '#EC4899',
                'sort_order' => 16,
            ],
            [
                'name' => 'Health',
                'lt_name' => 'Sveikata',
                'description' => 'Health and wellness content',
                'lt_description' => 'Sveikatos ir gerovės turinys',
                'color' => '#10B981',
                'sort_order' => 17,
            ],
            [
                'name' => 'Science',
                'lt_name' => 'Mokslas',
                'description' => 'Science and research news',
                'lt_description' => 'Mokslo ir tyrimų naujienos',
                'color' => '#3B82F6',
                'sort_order' => 18,
            ],
            [
                'name' => 'Politics',
                'lt_name' => 'Politika',
                'description' => 'Political news and analysis',
                'lt_description' => 'Politikos naujienos ir analizės',
                'color' => '#EF4444',
                'sort_order' => 19,
            ],
            [
                'name' => 'Education',
                'lt_name' => 'Švietimas',
                'description' => 'Education and learning content',
                'lt_description' => 'Švietimo ir mokymosi turinys',
                'color' => '#8B5CF6',
                'sort_order' => 20,
            ],
        ];

        foreach ($tagData as $index => $tagInfo) {
            $newsTag = NewsTag::factory()->create([
                'is_visible' => true,
                'color' => $tagInfo['color'],
                'sort_order' => $tagInfo['sort_order'] ?? ($index + 1),
            ]);

            // Create translations for each locale
            foreach ($locales as $locale) {
                $name = $locale === 'lt' ? $tagInfo['lt_name'] : $tagInfo['name'];
                $description = $locale === 'lt' ? $tagInfo['lt_description'] : $tagInfo['description'];
                $slug = str($name)->slug()->toString().'-'.$newsTag->id;

                NewsTagTranslation::updateOrCreate([
                    'news_tag_id' => $newsTag->id,
                    'locale' => $locale,
                ], [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                ]);
            }
        }

        $this->command->info('News tags seeded successfully!');
    }
}
