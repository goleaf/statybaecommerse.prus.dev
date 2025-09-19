<?php declare(strict_types=1);

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
        // Get all zones to create shipping options for each
        $zones = Zone::all();

        if ($zones->isEmpty()) {
            $this->command->warn('No zones found. Please run ZoneSeeder first.');
            return;
        }

        foreach ($zones as $zone) {
            // Create multiple shipping options for each zone
            $shippingOptions = [
                [
                    'name' => 'DHL Express',
                    'slug' => 'dhl-express-' . $zone->id,
                    'description' => 'Fast and reliable express delivery',
                    'carrier_name' => 'DHL',
                    'service_type' => 'Express',
                    'price' => 15.99,
                    'currency_code' => 'EUR',
                    'is_enabled' => true,
                    'is_default' => true,
                    'sort_order' => 1,
                    'estimated_days_min' => 1,
                    'estimated_days_max' => 2,
                ],
                [
                    'name' => 'DHL Standard',
                    'slug' => 'dhl-standard-' . $zone->id,
                    'description' => 'Standard delivery service',
                    'carrier_name' => 'DHL',
                    'service_type' => 'Standard',
                    'price' => 9.99,
                    'currency_code' => 'EUR',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 2,
                    'estimated_days_min' => 3,
                    'estimated_days_max' => 5,
                ],
                [
                    'name' => 'UPS Economy',
                    'slug' => 'ups-economy-' . $zone->id,
                    'description' => 'Economical delivery option',
                    'carrier_name' => 'UPS',
                    'service_type' => 'Economy',
                    'price' => 6.99,
                    'currency_code' => 'EUR',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 3,
                    'estimated_days_min' => 5,
                    'estimated_days_max' => 7,
                ],
                [
                    'name' => 'Free Shipping',
                    'slug' => 'free-shipping-' . $zone->id,
                    'description' => 'Free shipping for orders over €50',
                    'carrier_name' => 'Standard',
                    'service_type' => 'Free',
                    'price' => 0.0,
                    'currency_code' => 'EUR',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 4,
                    'min_order_amount' => 50.0,
                    'estimated_days_min' => 7,
                    'estimated_days_max' => 10,
                ],
            ];

            foreach ($shippingOptions as $option) {
                ShippingOption::updateOrCreate(
                    [
                        'slug' => $option['slug'],
                        'zone_id' => $zone->id,
                    ],
                    array_merge($option, ['zone_id' => $zone->id])
                );
            }
        }

        $this->command->info('Shipping options seeded successfully for ' . $zones->count() . ' zones.');
    }
}
