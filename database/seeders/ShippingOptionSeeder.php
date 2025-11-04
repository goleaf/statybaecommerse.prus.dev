<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\ShippingOption;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ShippingOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Zone::count() === 0) {
            Zone::factory()->count(3)->create([
                'is_enabled' => true,
            ]);
        }

        $zones = Zone::query()->where('is_enabled', true)->get();

        $countries = Country::query()->pluck('id');

        if ($countries->isEmpty()) {
            $fallbackCountry = Country::query()->create([
                'name'               => 'Lithuania',
                'name_official'      => 'Lietuvos Respublika',
                'description'        => 'Baltijos regiono valstybė.',
                'cca2'               => 'LT',
                'cca3'               => 'LTU',
                'ccn3'               => '440',
                'code'               => 'LT',
                'iso_code'           => 'LT',
                'currency_code'      => 'EUR',
                'currency_symbol'    => '€',
                'phone_code'         => '370',
                'phone_calling_code' => '370',
                'flag'               => 'lt.png',
                'svg_flag'           => 'lt.svg',
                'region'             => 'Europe',
                'subregion'          => 'Northern Europe',
                'latitude'           => 55.1694,
                'longitude'          => 23.8813,
                'currencies'         => ['EUR' => 'Euro'],
                'languages'          => ['lt' => 'Lithuanian', 'en' => 'English'],
                'timezones'          => ['Europe/Vilnius' => 'Vilnius'],
                'timezone'           => 'Europe/Vilnius',
                'is_active'          => true,
                'is_enabled'         => true,
                'is_eu_member'       => true,
                'requires_vat'       => true,
                'vat_rate'           => 21.0,
                'metadata'           => ['capital' => 'Vilnius'],
                'sort_order'         => 1,
            ]);

            $countries = collect([$fallbackCountry->id]);
        }

        $cities = City::query()->whereIn('country_id', $countries)->pluck('id');

        if ($cities->isEmpty()) {
            $defaultCountry = Country::query()->find($countries->first());

            $city = City::factory()
                ->for($defaultCountry)
                ->create([
                    'name'        => 'Vilnius',
                    'slug'        => 'vilnius',
                    'code'        => 'VNO',
                    'description' => 'Vilnius miestas',
                    'is_capital'  => true,
                ]);

            $cities = collect([$city->id]);
        }

        foreach ($zones as $zone) {
            // Create DHL Express (default option)
            ShippingOption::factory()
                ->for($zone)
                ->state([
                    'name'               => 'DHL Express',
                    'slug'               => 'dhl-express-' . $zone->id,
                    'description'        => 'Fast and reliable express delivery',
                    'carrier_name'       => 'DHL',
                    'service_type'       => 'Express',
                    'price'              => 15.99,
                    'is_default'         => true,
                    'sort_order'         => 1,
                    'estimated_days_min' => 1,
                    'estimated_days_max' => 2,
                    'country_id'         => $countries->random(),
                    'city_id'            => $cities->random(),
                ])
                ->create();

            // Create DHL Standard
            ShippingOption::factory()
                ->for($zone)
                ->state([
                    'name'               => 'DHL Standard',
                    'slug'               => 'dhl-standard-' . $zone->id,
                    'description'        => 'Standard delivery service',
                    'carrier_name'       => 'DHL',
                    'service_type'       => 'Standard',
                    'price'              => 9.99,
                    'sort_order'         => 2,
                    'estimated_days_min' => 3,
                    'estimated_days_max' => 5,
                    'country_id'         => $countries->random(),
                    'city_id'            => $cities->random(),
                ])
                ->create();

            // Create UPS Economy
            ShippingOption::factory()
                ->for($zone)
                ->state([
                    'name'               => 'UPS Economy',
                    'slug'               => 'ups-economy-' . $zone->id,
                    'description'        => 'Economical delivery option',
                    'carrier_name'       => 'UPS',
                    'service_type'       => 'Economy',
                    'price'              => 6.99,
                    'sort_order'         => 3,
                    'estimated_days_min' => 5,
                    'estimated_days_max' => 7,
                    'country_id'         => $countries->random(),
                    'city_id'            => $cities->random(),
                ])
                ->create();

            // Create Free Shipping
            ShippingOption::factory()
                ->for($zone)
                ->free()
                ->state([
                    'slug'               => 'free-shipping-' . $zone->id,
                    'description'        => 'Free shipping for orders over €50',
                    'sort_order'         => 4,
                    'min_order_amount'   => 50.0,
                    'estimated_days_min' => 7,
                    'estimated_days_max' => 10,
                    'country_id'         => $countries->random(),
                    'city_id'            => $cities->random(),
                ])
                ->create();
        }

        $this->command->info('Shipping options seeded successfully for ' . $zones->count() . ' zones.');
    }
}
