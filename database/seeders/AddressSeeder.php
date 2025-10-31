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
        $countries = Country::query()->pluck('cca2')->all();

        if (empty($countries)) {
            return;
        }

        $users = User::query()->limit(25)->get();

        if ($users->isEmpty()) {
            $users = User::factory()->count(5)->create();
        }

        $users->each(function (User $user) use ($countries): void {
            if ($user->addresses()->exists()) {
                return;
            }

            $types = [
                AddressType::SHIPPING,
                AddressType::BILLING,
                AddressType::HOME,
                AddressType::WORK,
                AddressType::OTHER,
                AddressType::OTHER,
            ];

            Address::factory()
                ->count(6)
                ->sequence(function ($sequence) use ($countries, $types): array {
                    $index = $sequence->index;
                    $type = $types[$index] ?? AddressType::OTHER;

                    return [
                        'type'         => $type,
                        'country_code' => $countries[$index % count($countries)],
                        'is_default'   => $index === 0,
                        'is_shipping'  => in_array($type, [AddressType::SHIPPING, AddressType::HOME], true),
                        'is_billing'   => $type === AddressType::BILLING,
                        'is_active'    => $index !== 5,
                    ];
                })
                ->for($user)
                ->create();
        });
    }
}
