<?php declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Setting;

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
        $countryId = \App\Models\Country::query()->where('code', 'LT')->value('id');
        if ($countryId) {
            $this->set('country_id', $countryId);
        }

        // Socials (optional)
        $this->set('facebook_link', 'https://facebook.com/' . Str::slug($name));
        $this->set('instagram_link', 'https://instagram.com/' . Str::slug($name));
        $this->set('twitter_link', 'https://twitter.com/' . Str::slug($name));

        // Default location if none exists
        if (!Location::query()->exists()) {
            Location::query()->create([
                'name' => $name,
                'code' => Str::slug($name),
                'email' => $email,
                'address_line_1' => 'Didžioji g. 1',
                'city' => 'Vilnius',
                'state' => 'Vilnius County',
                'postal_code' => '01128',
                'country_code' => 'LT',
                'phone' => '+37060000000',
                'is_default' => true,
                'is_enabled' => true,
                'type' => 'warehouse',
            ]);
        }
    }

    protected function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
