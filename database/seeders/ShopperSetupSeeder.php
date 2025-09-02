<?php declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Inventory;
use Shopper\Core\Models\Setting;

class ShopperSetupSeeder extends Seeder
{
    public function run(): void
    {
        // Basic store info
        $name = config('app.name', 'My Store');
        $email = config('app.admin_email', env('ADMIN_EMAIL', 'admin@example.com'));

        $this->set('name', $name);
        $this->set('email', $email);
        $this->set('about', 'Welcome to ' . $name . '.');

        // Country & currencies
        $defaultCurrency = Currency::query()->where('code', strtoupper(env('CURRENCY', 'EUR')))->first()
            ?: Currency::query()->where('code', 'EUR')->first();
        $defaultCurrencyId = $defaultCurrency?->id;
        if ($defaultCurrencyId) {
            $this->set('currencies', [$defaultCurrencyId]);
            $this->set('default_currency_id', $defaultCurrencyId);
        }

        // Address details
        $this->set('street_address', 'Didžioji g. 1');
        $this->set('postal_code', '01128');
        $this->set('city', 'Vilnius');
        $this->set('phone_number', '+37060000000');

        // Country id (Lithuania)
        $countryId = \Shop\Core\Models\Country::query()->where('cca2', 'LT')->value('id');
        if ($countryId) {
            $this->set('country_id', $countryId);
        }

        // Socials (optional)
        $this->set('facebook_link', 'https://facebook.com/' . Str::slug($name));
        $this->set('instagram_link', 'https://instagram.com/' . Str::slug($name));
        $this->set('twitter_link', 'https://twitter.com/' . Str::slug($name));

        // Default inventory if none exists
        if (!Inventory::query()->exists()) {
            Inventory::query()->create([
                'name' => $name,
                'code' => Str::slug($name),
                'email' => $email,
                'street_address' => shopper_setting('street_address'),
                'postal_code' => shopper_setting('postal_code'),
                'city' => shopper_setting('city'),
                'phone_number' => shopper_setting('phone_number'),
                'country_id' => shopper_setting('country_id'),
                'is_default' => true,
            ]);
        }
    }

    protected function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
