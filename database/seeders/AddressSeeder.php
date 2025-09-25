<?php declare(strict_types=1);

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

            Address::factory()
                ->count(6)
                ->state(function (int $sequence) use ($countries): array {
                    $types = [
                        AddressType::SHIPPING,
                        AddressType::BILLING,
                        AddressType::HOME,
                        AddressType::WORK,
                        AddressType::OTHER,
                        AddressType::OTHER,
                    ];

                    $type = $types[$sequence] ?? AddressType::OTHER;

                    return [
                        'type' => $type,
                        'country_code' => $countries[$sequence % count($countries)],
                        'is_default' => $sequence === 0,
                        'is_shipping' => in_array($type, [AddressType::SHIPPING, AddressType::HOME], true),
                        'is_billing' => $type === AddressType::BILLING,
                        'is_active' => $sequence !== 5,
                    ];
                })
                ->for($user)
                ->create();
        });
    }
}
