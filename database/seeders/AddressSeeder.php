<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * AddressSeeder
 *
 * Seeder for creating sample address data
 */
final class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $countries = Country::all();

        if ($users->isEmpty() || $countries->isEmpty()) {
            $this->command->warn('No users or countries found. Please run UserSeeder and CountrySeeder first.');

            return;
        }

        // Create addresses for each user
        foreach ($users as $user) {
            // Create default shipping address
            Address::factory()->create([
                'user_id' => $user->id,
                'type' => AddressType::SHIPPING,
                'first_name' => $user->first_name ?? 'John',
                'last_name' => $user->last_name ?? 'Doe',
                'address_line_1' => '123 Main Street',
                'city' => 'Vilnius',
                'postal_code' => '01101',
                'country_code' => 'LT',
                'phone' => '+37060000000',
                'email' => $user->email,
                'is_default' => true,
                'is_active' => true,
                'is_shipping' => true,
                'is_billing' => false,
            ]);

            // Create billing address
            Address::factory()->create([
                'user_id' => $user->id,
                'type' => AddressType::BILLING,
                'first_name' => $user->first_name ?? 'John',
                'last_name' => $user->last_name ?? 'Doe',
                'address_line_1' => '456 Business Avenue',
                'city' => 'Kaunas',
                'postal_code' => '44200',
                'country_code' => 'LT',
                'phone' => '+37060000001',
                'email' => $user->email,
                'is_default' => false,
                'is_active' => true,
                'is_shipping' => false,
                'is_billing' => true,
            ]);

            // Create home address
            Address::factory()->create([
                'user_id' => $user->id,
                'type' => AddressType::HOME,
                'first_name' => $user->first_name ?? 'John',
                'last_name' => $user->last_name ?? 'Doe',
                'address_line_1' => '789 Residential Street',
                'city' => 'Klaipėda',
                'postal_code' => '91200',
                'country_code' => 'LT',
                'phone' => '+37060000002',
                'email' => $user->email,
                'is_default' => false,
                'is_active' => true,
                'is_shipping' => false,
                'is_billing' => false,
            ]);

            // Create work address
            Address::factory()->create([
                'user_id' => $user->id,
                'type' => AddressType::WORK,
                'first_name' => $user->first_name ?? 'John',
                'last_name' => $user->last_name ?? 'Doe',
                'company_name' => 'Tech Company Ltd',
                'company_vat' => 'LT123456789',
                'address_line_1' => '321 Corporate Boulevard',
                'city' => 'Šiauliai',
                'postal_code' => '76200',
                'country_code' => 'LT',
                'phone' => '+37060000003',
                'email' => $user->email,
                'is_default' => false,
                'is_active' => true,
                'is_shipping' => false,
                'is_billing' => false,
            ]);

            // Create other address
            Address::factory()->create([
                'user_id' => $user->id,
                'type' => AddressType::OTHER,
                'first_name' => $user->first_name ?? 'John',
                'last_name' => $user->last_name ?? 'Doe',
                'address_line_1' => '654 Alternative Road',
                'city' => 'Panevėžys',
                'postal_code' => '35100',
                'country_code' => 'LT',
                'phone' => '+37060000004',
                'email' => $user->email,
                'is_default' => false,
                'is_active' => true,
                'is_shipping' => false,
                'is_billing' => false,
            ]);

            // Create some inactive addresses
            Address::factory()->create([
                'user_id' => $user->id,
                'type' => AddressType::OTHER,
                'first_name' => $user->first_name ?? 'John',
                'last_name' => $user->last_name ?? 'Doe',
                'address_line_1' => '999 Old Street',
                'city' => 'Alytus',
                'postal_code' => '62100',
                'country_code' => 'LT',
                'phone' => '+37060000005',
                'email' => $user->email,
                'is_default' => false,
                'is_active' => false,
                'is_shipping' => false,
                'is_billing' => false,
            ]);
        }

        $this->command->info('Addresses seeded successfully!');
    }
}
