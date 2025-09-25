<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

final class GroupSeeder extends Seeder
{
    public function run(): void
    {
        // Create customer groups using factories
        $vipGroup = CustomerGroup::factory()->state([
            'name' => 'VIP',
            'code' => 'vip',
            'description' => 'VIP customers with exclusive benefits',
            'discount_percentage' => 15.0,
            'is_enabled' => true,
            'metadata' => [
                'priority' => 'high',
                'benefits' => ['free_shipping', 'priority_support', 'exclusive_products'],
            ],
        ])->create();

        $studentGroup = CustomerGroup::factory()->state([
            'name' => 'Student',
            'code' => 'student',
            'description' => 'Students with educational discounts',
            'discount_percentage' => 10.0,
            'is_enabled' => true,
            'metadata' => [
                'priority' => 'medium',
                'benefits' => ['student_discount', 'educational_resources'],
                'verification_required' => true,
            ],
        ])->create();

        $wholesaleGroup = CustomerGroup::factory()->state([
            'name' => 'Wholesale',
            'code' => 'wholesale',
            'description' => 'Wholesale customers with bulk pricing',
            'discount_percentage' => 25.0,
            'is_enabled' => true,
            'metadata' => [
                'priority' => 'high',
                'benefits' => ['bulk_pricing', 'extended_payment_terms', 'dedicated_support'],
                'minimum_order' => 1000,
            ],
        ])->create();

        // Create additional users if needed and attach them to groups
        $existingUsers = User::limit(20)->get();

        if ($existingUsers->isEmpty()) {
            // Create some users if none exist
            $existingUsers = User::factory()->count(15)->create();
        }

        // Attach users to groups using relationships
        $groups = collect([$vipGroup, $studentGroup, $wholesaleGroup]);

        foreach ($groups as $group) {
            // Attach random users to each group
            $usersToAttach = $existingUsers->random(min($existingUsers->count(), random_int(3, 8)));
            $group->users()->syncWithoutDetaching($usersToAttach->pluck('id'));
        }

        // Some users can be in multiple groups
        $multiGroupUsers = $existingUsers->random(min($existingUsers->count(), 5));
        foreach ($multiGroupUsers as $user) {
            $randomGroups = $groups->random(random_int(1, 2));
            $user->customerGroups()->syncWithoutDetaching($randomGroups->pluck('id'));
        }
    }
}
