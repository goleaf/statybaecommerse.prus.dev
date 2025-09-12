<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NormalSetting;
use App\Models\NormalSettingTranslation;
use Illuminate\Database\Seeder;

final class NormalSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'group' => 'general',
                'key' => 'site_name',
                'type' => 'text',
                'value' => 'Statyba E‑Commerce',
                'description' => 'Public site display name',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => ['required', 'string', 'max:255'],
                'sort_order' => 0,
            ],
            [
                'group' => 'general',
                'key' => 'default_locale',
                'type' => 'text',
                'value' => 'lt',
                'description' => 'Default locale code',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => ['required', 'string', 'max:10'],
                'sort_order' => 1,
            ],
            [
                'group' => 'ecommerce',
                'key' => 'default_currency',
                'type' => 'text',
                'value' => 'EUR',
                'description' => 'Default currency code',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => ['required', 'string', 'size:3'],
                'sort_order' => 0,
            ],
            [
                'group' => 'shipping',
                'key' => 'free_shipping_threshold',
                'type' => 'number',
                'value' => 100,
                'description' => 'Order total to qualify for free shipping',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => ['numeric', 'min:0'],
                'sort_order' => 0,
            ],
            [
                'group' => 'security',
                'key' => 'maintenance_mode',
                'type' => 'boolean',
                'value' => false,
                'description' => 'Enable maintenance mode',
                'is_public' => false,
                'is_encrypted' => false,
                'validation_rules' => ['boolean'],
                'sort_order' => 0,
            ],
        ];

        $locales = $this->supportedLocales();

        foreach ($settings as $data) {
            /** @var array{group:string,key:string} $data */
            $setting = NormalSetting::query()->updateOrCreate(
                ['group' => $data['group'], 'key' => $data['key']],
                $data,
            );

            // Create translations for each locale
            foreach ($locales as $locale) {
                NormalSettingTranslation::updateOrCreate([
                    'enhanced_setting_id' => $setting->id,
                    'locale' => $locale,
                ], [
                    'description' => $this->getTranslatedDescription($data['description'], $locale),
                    'display_name' => $this->getTranslatedDisplayName($data['key'], $locale),
                    'help_text' => $this->getTranslatedHelpText($data['key'], $locale),
                ]);
            }
        }

        $this->command?->info('NormalSettingSeeder: seeded settings with translations (locales: '.implode(',', $locales).').');
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

    private function getTranslatedDescription(string $description, string $locale): string
    {
        $translations = [
            'Public site display name' => [
                'lt' => 'Svetainės viešas pavadinimas',
                'en' => 'Public site display name',
            ],
            'Default locale code' => [
                'lt' => 'Numatytasis lokalės kodas',
                'en' => 'Default locale code',
            ],
            'Default currency code' => [
                'lt' => 'Numatytasis valiutos kodas',
                'en' => 'Default currency code',
            ],
            'Free shipping threshold amount' => [
                'lt' => 'Nemokamo pristatymo riba',
                'en' => 'Free shipping threshold amount',
            ],
            'Default tax rate percentage' => [
                'lt' => 'Numatytasis PVM tarifas procentais',
                'en' => 'Default tax rate percentage',
            ],
            'Enable product reviews' => [
                'lt' => 'Įjungti produktų atsiliepimus',
                'en' => 'Enable product reviews',
            ],
            'Enable wishlist feature' => [
                'lt' => 'Įjungti pageidavimų sąrašo funkciją',
                'en' => 'Enable wishlist feature',
            ],
            'Enable guest checkout' => [
                'lt' => 'Leisti svečių užsakymus',
                'en' => 'Enable guest checkout',
            ],
            'Minimum order amount' => [
                'lt' => 'Minimalus užsakymo dydis',
                'en' => 'Minimum order amount',
            ],
            'Low stock threshold' => [
                'lt' => 'Mažų atsargų riba',
                'en' => 'Low stock threshold',
            ],
        ];

        return $translations[$description][$locale] ?? $description;
    }

    private function getTranslatedDisplayName(string $key, string $locale): string
    {
        $translations = [
            'site_name' => [
                'lt' => 'Svetainės pavadinimas',
                'en' => 'Site Name',
            ],
            'default_locale' => [
                'lt' => 'Numatytoji kalba',
                'en' => 'Default Locale',
            ],
            'default_currency' => [
                'lt' => 'Numatytoji valiuta',
                'en' => 'Default Currency',
            ],
            'free_shipping_threshold' => [
                'lt' => 'Nemokamo pristatymo riba',
                'en' => 'Free Shipping Threshold',
            ],
            'default_tax_rate' => [
                'lt' => 'Numatytasis PVM tarifas',
                'en' => 'Default Tax Rate',
            ],
            'enable_reviews' => [
                'lt' => 'Įjungti atsiliepimus',
                'en' => 'Enable Reviews',
            ],
            'enable_wishlist' => [
                'lt' => 'Įjungti pageidavimų sąrašą',
                'en' => 'Enable Wishlist',
            ],
            'enable_guest_checkout' => [
                'lt' => 'Leisti svečių užsakymus',
                'en' => 'Enable Guest Checkout',
            ],
            'minimum_order_amount' => [
                'lt' => 'Minimalus užsakymo dydis',
                'en' => 'Minimum Order Amount',
            ],
            'low_stock_threshold' => [
                'lt' => 'Mažų atsargų riba',
                'en' => 'Low Stock Threshold',
            ],
        ];

        return $translations[$key][$locale] ?? $key;
    }

    private function getTranslatedHelpText(string $key, string $locale): string
    {
        $translations = [
            'site_name' => [
                'lt' => 'Pavadinimas, kuris bus rodomas svetainės antraštėje ir kitose vietose',
                'en' => 'Name that will be displayed in the site header and other places',
            ],
            'default_locale' => [
                'lt' => 'Numatytoji kalba, kuri bus naudojama naujiems vartotojams',
                'en' => 'Default language that will be used for new users',
            ],
            'default_currency' => [
                'lt' => 'Numatytoji valiuta, kuria bus rodomos kainos',
                'en' => 'Default currency in which prices will be displayed',
            ],
            'free_shipping_threshold' => [
                'lt' => 'Minimalus užsakymo dydis, nuo kurio pristatymas bus nemokamas',
                'en' => 'Minimum order amount from which shipping will be free',
            ],
            'default_tax_rate' => [
                'lt' => 'Numatytasis PVM tarifas procentais, kuris bus taikomas prekėms be specifinio tarifo',
                'en' => 'Default tax rate percentage that will be applied to products without specific rate',
            ],
            'enable_reviews' => [
                'lt' => 'Leisti klientams palikti atsiliepimus apie prekes',
                'en' => 'Allow customers to leave reviews about products',
            ],
            'enable_wishlist' => [
                'lt' => 'Leisti klientams kurti pageidavimų sąrašus',
                'en' => 'Allow customers to create wishlists',
            ],
            'enable_guest_checkout' => [
                'lt' => 'Leisti užsakymus be registracijos',
                'en' => 'Allow orders without registration',
            ],
            'minimum_order_amount' => [
                'lt' => 'Minimalus užsakymo dydis, kuris reikalingas užsakymo patvirtinimui',
                'en' => 'Minimum order amount required for order confirmation',
            ],
            'low_stock_threshold' => [
                'lt' => 'Atsargų kiekis, nuo kurio bus rodomas įspėjimas apie mažas atsargas',
                'en' => 'Stock quantity from which a low stock warning will be displayed',
            ],
        ];

        return $translations[$key][$locale] ?? '';
    }
}
