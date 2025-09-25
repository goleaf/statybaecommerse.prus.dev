<?php

declare(strict_types=1);

namespace Database\Seeders;

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
        // Ensure we have zones to work with
        if (Zone::count() === 0) {
            // Create default zones if none exist
            Zone::factory()->count(3)->create([
                'is_enabled' => true,
            ]);
        }

        $zones = Zone::where('is_enabled', true)->get();

        foreach ($zones as $zone) {
            // Create DHL Express (default option)
            ShippingOption::factory()
                ->for($zone)
                ->state([
                    'name' => 'DHL Express',
                    'slug' => 'dhl-express-' . $zone->id,
                    'description' => 'Fast and reliable express delivery',
                    'carrier_name' => 'DHL',
                    'service_type' => 'Express',
                    'price' => 15.99,
                    'is_default' => true,
                    'sort_order' => 1,
                    'estimated_days_min' => 1,
                    'estimated_days_max' => 2,
                ])
                ->create();

            // Create DHL Standard
            ShippingOption::factory()
                ->for($zone)
                ->state([
                    'name' => 'DHL Standard',
                    'slug' => 'dhl-standard-' . $zone->id,
                    'description' => 'Standard delivery service',
                    'carrier_name' => 'DHL',
                    'service_type' => 'Standard',
                    'price' => 9.99,
                    'sort_order' => 2,
                    'estimated_days_min' => 3,
                    'estimated_days_max' => 5,
                ])
                ->create();

            // Create UPS Economy
            ShippingOption::factory()
                ->for($zone)
                ->state([
                    'name' => 'UPS Economy',
                    'slug' => 'ups-economy-' . $zone->id,
                    'description' => 'Economical delivery option',
                    'carrier_name' => 'UPS',
                    'service_type' => 'Economy',
                    'price' => 6.99,
                    'sort_order' => 3,
                    'estimated_days_min' => 5,
                    'estimated_days_max' => 7,
                ])
                ->create();

            // Create Free Shipping
            ShippingOption::factory()
                ->for($zone)
                ->free()
                ->state([
                    'slug' => 'free-shipping-' . $zone->id,
                    'description' => 'Free shipping for orders over €50',
                    'sort_order' => 4,
                    'min_order_amount' => 50.0,
                    'estimated_days_min' => 7,
                    'estimated_days_max' => 10,
                ])
                ->create();
        }

        $this->command->info('Shipping options seeded successfully for ' . $zones->count() . ' zones.');
    }
}
