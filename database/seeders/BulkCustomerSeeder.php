<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
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

        $existingCustomerIndex = $this->resolveHighestCustomerIndex();

        $localizedSequence = new Sequence(function (Sequence $sequence) use ($existingCustomerIndex): array {
            $runningIndex = $existingCustomerIndex + $sequence->index + 1;
            $formattedIndex = str_pad((string) $runningIndex, 5, '0', STR_PAD_LEFT);

            // Prefix generated customers with a deterministic label so the feature
            // tests can make stable assertions without relying on faker output.
            $displayName = "Customer {$formattedIndex}";

            return [
                'name'             => $displayName,
                'first_name'       => 'Customer',
                'last_name'        => $formattedIndex,
                'email'            => "customer{$formattedIndex}@example.com",
                'preferred_locale' => $sequence->index % 2 === 0 ? 'lt' : 'en',
            ];
        });

        User::factory()
            ->count($remaining)
            ->state($localizedSequence)
            ->shippingAddress()
            ->billingAddress()
            ->afterCreating(function (User $user) use ($defaultGroup): void {
                // Keep the generated address names aligned with the owning customer so
                // downstream UI components show consistent personalisation.
                $user->addresses
                    ->each(function (Address $address) use ($user): void {
                        $address->update([
                            'first_name' => $user->first_name,
                            'last_name'  => $user->last_name,
                            'email'      => $user->email,
                        ]);
                    });

                if ($defaultGroup === null) {
                    return;
                }

                // Capture an assignment timestamp alongside the default pivot timestamps
                // so audit trails and analytics can track when the user joined the group.
                $user->customerGroups()->syncWithoutDetaching([
                    $defaultGroup->getKey() => [
                        'assigned_at' => now(),
                        'created_at'  => now(),
                        'updated_at'  => now(),
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
            'slug'                => 'default-customer-group',
            'code'                => 'DEFAULT',
            'discount_percentage' => 0,
            'metadata'            => ['type' => 'regular'],
        ]);
    }

    private function resolveHighestCustomerIndex(): int
    {
        // Parse existing deterministic emails so rerunning the seeder never collides
        // with pre-seeded customer accounts that already follow the convention.
        return User::query()
            ->where('email', 'like', 'customer%@example.com')
            ->pluck('email')
            ->map(static function (string $email): int {
                if (preg_match('/^customer(\d{5})@example\.com$/', $email, $matches) === 1) {
                    return (int) $matches[1];
                }

                return 0;
            })
            ->max() ?? 0;
    }
}
