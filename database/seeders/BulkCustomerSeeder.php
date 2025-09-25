<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

final class BulkCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $targetCount = (int) env('BULK_CUSTOMER_COUNT', 100);

        $existingCustomers = User::query()->where('is_admin', false)->count();
        $remaining = max($targetCount - $existingCustomers, 0);

        if ($remaining === 0) {
            $this->command?->info('BulkCustomerSeeder: target customer count already satisfied.');

            return;
        }

        $defaultGroup = $this->resolveDefaultGroup();

        $localeCycle = new Sequence(
            ['preferred_locale' => 'lt'],
            ['preferred_locale' => 'en']
        );

        User::factory()
            ->count($remaining)
            ->state($localeCycle)
            ->shippingAddress()
            ->billingAddress()
            ->afterCreating(function (User $user) use ($defaultGroup): void {
                if ($defaultGroup === null) {
                    return;
                }

                $user->customerGroups()->syncWithoutDetaching([
                    $defaultGroup->getKey() => [
                        'assigned_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            })
            ->create();
    }

    private function resolveDefaultGroup(): ?CustomerGroup
    {
        $defaultGroup = CustomerGroup::query()->first();

        if ($defaultGroup !== null) {
            return $defaultGroup;
        }

        return CustomerGroup::factory()->create([
            'name' => [
                'lt' => 'Numatytoji klientų grupė',
                'en' => 'Default Customer Group',
            ],
            'description' => [
                'lt' => 'Standartinė grupė visiems naujiems klientams.',
                'en' => 'Default segment for all newly created customers.',
            ],
            'slug' => 'default-customer-group',
            'code' => 'DEFAULT',
            'discount_percentage' => 0,
            'metadata' => ['type' => 'regular'],
        ]);
    }
}
