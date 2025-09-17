<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Legal;
use App\Models\Translations\LegalTranslation;
use Illuminate\Database\Seeder;

class LegalSeeder extends Seeder
{
    public function run(): void
    {
        $locales = $this->supportedLocales();

        $legalDocuments = [
            [
                'key' => 'privacy',
                'title' => [
                    'lt' => 'Privatumo politika',
                    'en' => 'Privacy Policy',
                ],
                'content' => [
                    'lt' => 'Privatumo politikos turinys lietuvių kalba...',
                    'en' => 'Privacy policy content in English...',
                ],
            ],
            [
                'key' => 'terms',
                'title' => [
                    'lt' => 'Naudojimosi sąlygos',
                    'en' => 'Terms of Use',
                ],
                'content' => [
                    'lt' => 'Naudojimosi sąlygų turinys lietuvių kalba...',
                    'en' => 'Terms of use content in English...',
                ],
            ],
            [
                'key' => 'refund',
                'title' => [
                    'lt' => 'Grąžinimo politika',
                    'en' => 'Refund Policy',
                ],
                'content' => [
                    'lt' => 'Grąžinimo politikos turinys lietuvių kalba...',
                    'en' => 'Refund policy content in English...',
                ],
            ],
            [
                'key' => 'shipping',
                'title' => [
                    'lt' => 'Pristatymo politika',
                    'en' => 'Shipping Policy',
                ],
                'content' => [
                    'lt' => 'Pristatymo politikos turinys lietuvių kalba...',
                    'en' => 'Shipping policy content in English...',
                ],
            ],
        ];

        foreach ($legalDocuments as $document) {
            $legal = Legal::query()->updateOrCreate(
                ['key' => $document['key']],
                [
                    'is_enabled' => true,
                ]
            );

            // Create translations for each locale
            foreach ($locales as $locale) {
                $title = $document['title'][$locale] ?? $document['title']['lt'];
                $slug = \Illuminate\Support\Str::slug($title).'-'.$locale;

                LegalTranslation::updateOrCreate([
                    'legal_id' => $legal->id,
                    'locale' => $locale,
                ], [
                    'title' => $title,
                    'content' => $document['content'][$locale] ?? $document['content']['lt'],
                    'slug' => $slug,
                ]);
            }
        }

        $this->command?->info('LegalSeeder: seeded legal documents with translations (locales: '.implode(',', $locales).').');
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
}
