<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Legal;
use App\Models\Translations\LegalTranslation;

class LegalSeeder extends BaseSeeder
{
    public function run(): void
    {
        $locales = $this->supportedLocales();

        $legalDocuments = [
            [
                'key'      => 'privacy-policy',
                'type'     => 'privacy_policy',
                'required' => true,
                'title'    => [
                    'lt' => 'Privatumo politika',
                    'en' => 'Privacy Policy',
                ],
                'content' => [
                    'lt' => '<p>Ši privatumo politika paaiškina, kaip renkame, naudojame ir saugome jūsų asmens duomenis naudojantis mūsų svetaine bei paslaugomis.</p>',
                    'en' => '<p>This privacy policy explains how we collect, use, and protect your personal data when using our website and services.</p>',
                ],
            ],
            [
                'key'      => 'terms-of-use',
                'type'     => 'terms_of_use',
                'required' => true,
                'title'    => [
                    'lt' => 'Naudojimosi sąlygos',
                    'en' => 'Terms of Use',
                ],
                'content' => [
                    'lt' => '<p>Naudodamiesi svetaine sutinkate su šiomis sąlygomis. Prieš pateikdami užsakymą, susipažinkite su visa sąlygų redakcija.</p>',
                    'en' => '<p>By using this website, you agree to these terms. Please review the full terms before placing an order.</p>',
                ],
            ],
            [
                'key'      => 'return-policy',
                'type'     => 'refund_policy',
                'required' => false,
                'title'    => [
                    'lt' => 'Grąžinimo politika',
                    'en' => 'Return Policy',
                ],
                'content' => [
                    'lt' => '<p>Prekių grąžinimas vykdomas pagal galiojančius teisės aktus. Grąžinimo terminai ir sąlygos nurodomi šiame dokumente.</p>',
                    'en' => '<p>Returns are handled according to applicable law. Return timelines and conditions are detailed in this policy.</p>',
                ],
            ],
            [
                'key'      => 'shipping-policy',
                'type'     => 'shipping_policy',
                'required' => false,
                'title'    => [
                    'lt' => 'Pristatymo politika',
                    'en' => 'Shipping Policy',
                ],
                'content' => [
                    'lt' => '<p>Pristatymo terminai priklauso nuo sandėlio likučio ir pasirinkto pristatymo būdo. Tiksli informacija pateikiama užsakymo metu.</p>',
                    'en' => '<p>Delivery timelines depend on stock availability and selected shipping method. Exact details are provided during checkout.</p>',
                ],
            ],
            [
                'key'      => 'cookie-policy',
                'type'     => 'cookie_policy',
                'required' => false,
                'title'    => [
                    'lt' => 'Slapukų politika',
                    'en' => 'Cookie Policy',
                ],
                'content' => [
                    'lt' => '<p>Slapukus naudojame svetainės funkcionalumui, analitikai ir turinio personalizavimui. Daugiau informacijos rasite šiame dokumente.</p>',
                    'en' => '<p>We use cookies for core website functionality, analytics, and content personalization. Learn more in this document.</p>',
                ],
            ],
        ];

        foreach ($legalDocuments as $index => $document) {
            $legal = Legal::query()->updateOrCreate(
                ['key' => $document['key']],
                [
                    'type'         => $document['type'],
                    'is_enabled'   => true,
                    'is_required'  => (bool) $document['required'],
                    'sort_order'   => $index + 1,
                    'published_at' => now(),
                ]
            );

            // Create translations for each locale
            foreach ($locales as $locale) {
                $title = $document['title'][$locale] ?? $document['title']['lt'];
                $slug = \Illuminate\Support\Str::slug($title) . '-' . $locale;

                LegalTranslation::updateOrCreate([
                    'legal_id' => $legal->id,
                    'locale'   => $locale,
                ], [
                    'title'   => $title,
                    'content' => $document['content'][$locale] ?? $document['content']['lt'],
                    'slug'    => $slug,
                ]);
            }
        }

        $this->command?->info('LegalSeeder: seeded legal documents with translations (locales: ' . implode(',', $locales) . ').');
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
