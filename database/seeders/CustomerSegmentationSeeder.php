<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

final class CustomerSegmentationSeeder extends Seeder
{
    public function run(): void
    {
        $customerGroups = [
            [
                'name' => 'VIP Customers',
                'slug' => 'vip-customers',
                'description' => 'High-value customers with special privileges and exclusive access to premium products',
                'discount_percentage' => 15.0,
                'is_enabled' => true,
                'is_active' => true,
                'conditions' => [
                    'min_order_value' => 1000,
                    'min_orders' => 10,
                    'loyalty_months' => 12
                ],
            ],
            [
                'name' => 'Regular Customers',
                'slug' => 'regular-customers',
                'description' => 'Standard customers with basic benefits and standard pricing',
                'discount_percentage' => 5.0,
                'is_enabled' => true,
                'is_active' => true,
                'conditions' => [
                    'min_order_value' => 100,
                    'min_orders' => 3
                ],
            ],
            [
                'name' => 'New Customers',
                'slug' => 'new-customers',
                'description' => 'First-time customers with welcome offers and special introductory pricing',
                'discount_percentage' => 10.0,
                'is_enabled' => true,
                'is_active' => true,
                'conditions' => [
                    'max_orders' => 1,
                    'registration_days' => 30
                ],
            ],
            [
                'name' => 'Bulk Buyers',
                'slug' => 'bulk-buyers',
                'description' => 'Customers who purchase large quantities with volume discounts',
                'discount_percentage' => 12.0,
                'is_enabled' => true,
                'is_active' => true,
                'conditions' => [
                    'min_quantity' => 50,
                    'min_order_value' => 500
                ],
            ],
            [
                'name' => 'Corporate Clients',
                'slug' => 'corporate-clients',
                'description' => 'Business customers with negotiated pricing and special terms',
                'discount_percentage' => 20.0,
                'is_enabled' => true,
                'is_active' => true,
                'conditions' => [
                    'customer_type' => 'business',
                    'min_monthly_volume' => 5000
                ],
            ],
            [
                'name' => 'Inactive Customers',
                'slug' => 'inactive-customers',
                'description' => "Customers who haven't made a purchase in the last 6 months",
                'discount_percentage' => 0.0,
                'is_enabled' => false,
                'is_active' => false,
                'conditions' => [
                    'last_order_days' => 180,
                    'reactivation_campaign' => true
                ],
            ],
        ];

        foreach ($customerGroups as $groupData) {
            CustomerGroup::updateOrCreate(
                ['slug' => $groupData['slug']],
                $groupData
            );
        }

        // Assign some users to customer groups
        $users = User::limit(10)->get();
        $groups = CustomerGroup::where('is_enabled', true)->get();

        foreach ($users as $index => $user) {
            if ($groups->count() > 0) {
                $groupIndex = $index % $groups->count();
                $user->customerGroups()->syncWithoutDetaching([$groups[$groupIndex]->id]);
            }
        }
    }
}
