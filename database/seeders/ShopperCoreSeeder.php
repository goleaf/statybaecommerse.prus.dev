<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopperCoreSeeder extends Seeder
{
    public function run(): void
    {
        // Seed reference data manually (replacing Shopper core seeders)
        if (Country::query()->count() === 0) {
            $this->call([CountriesTableSeeder::class]);
        }
        $this->seedCurrencies();

        // Settings: General and socials minimal defaults (skip for now as Setting model may not exist)
        // $this->setting('store_name', config('app.name', 'Web Store'));
        // $this->setting('store_email', config('mail.from.address'));

        // Channel
        if (Channel::query()->count() === 0) {
            Channel::query()->create([
                'name' => 'Web Store',
                'url' => config('app.url'),
                'is_default' => true,
            ]);
        }

        // Ensure currencies are present (no-op if vendor seeder already ran)
        $this->seedCurrencies();

        $defaultCurrency = Currency::query()->where('code', 'EUR')->first();
        // Set default currency (skip settings for now)
        if ($defaultCurrency) {
            // $this->setting('default_currency_id', (string) $defaultCurrency->id);
            // $this->setting('currencies', json_encode(Currency::query()->whereIn('code', ['EUR'])->pluck('id')->all()));
        }

        // Location (using our new Location model)
        if (Location::query()->count() === 0) {
            Location::query()->create([
                'name' => 'Pagrindinis Sandėlis',
                'code' => 'SNDL',
                'address_line_1' => 'Didžioji g. 1',
                'city' => 'Vilnius',
                'state' => 'Vilnius',
                'postal_code' => '01128',
                'country_code' => 'LT',
                'phone' => '+37060000000',
                'email' => 'store@example.com',
                'is_enabled' => true,
                'is_default' => true,
                'type' => 'warehouse',
            ]);
        }

        // Zone with EUR currency for Lithuania if missing
        if (Zone::query()->count() === 0 && $defaultCurrency) {
            Zone::query()->create([
                'name' => 'Lithuania',
                'slug' => 'lithuania',
                'code' => 'LT',
                'is_enabled' => true,
                'metadata' => ['description' => 'Lithuania shipping zone'],
                'currency_id' => $defaultCurrency->id,
                'tax_rate' => 21.0,  // Lithuania VAT rate
                'shipping_rate' => 5.0,
                'is_default' => true,
            ]);
        }
    }

    protected function seedCurrencies(): void
    {
        if (Currency::query()->count() > 0) {
            return;
        }

        $currencies = [
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'format' => '€#,##0.00', 'exchange_rate' => 1.0, 'is_enabled' => true, 'is_default' => true, 'decimal_places' => 2],
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'format' => '$#,##0.00', 'exchange_rate' => 1.1, 'is_enabled' => true, 'is_default' => false, 'decimal_places' => 2],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£', 'format' => '£#,##0.00', 'exchange_rate' => 0.85, 'is_enabled' => false, 'is_default' => false, 'decimal_places' => 2],
        ];

        foreach ($currencies as $currency) {
            DB::table('currencies')->updateOrInsert(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
