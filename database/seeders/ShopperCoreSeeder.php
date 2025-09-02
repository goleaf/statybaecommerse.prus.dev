<?php declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Channel;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Inventory;
use App\Models\Zone;

class ShopperCoreSeeder extends Seeder
{
    public function run(): void
    {
        // Seed reference data from Shopper core (conditionally to avoid duplicates)
        if (Country::query()->count() === 0) {
            $this->call([\Shop\Core\Database\Seeders\CountriesTableSeeder::class]);
        }
        if (Currency::query()->count() === 0) {
            $this->call([\Shop\Core\Database\Seeders\CurrenciesTableSeeder::class]);
        }

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
        if (\App\Models\Location::query()->count() === 0) {
            \App\Models\Location::query()->create([
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
                'code' => 'LT',
                'is_enabled' => true,
                'currency_id' => $defaultCurrency->id,
                'tax_rate' => 21.0, // Lithuania VAT rate
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

        $dataPath = base_path('vendor/shopper/core/database/data/currencies.php');
        if (file_exists($dataPath)) {
            /** @var array<string, array{name:string,symbol:string,format:string,exchange_rate:float}> $currencies */
            $currencies = include $dataPath;
            foreach ($currencies as $code => $currency) {
                Currency::query()->updateOrCreate([
                    'code' => $code,
                ], [
                    'name' => $currency['name'],
                    'symbol' => $currency['symbol'],
                    'format' => $currency['format'],
                    'exchange_rate' => $currency['exchange_rate'] ?? 1.0,
                    'is_enabled' => in_array($code, ['EUR'], true),
                ]);
            }
        }
    }
}
