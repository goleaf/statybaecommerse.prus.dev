<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'code' => 'RETAIL',
                'type' => 'retail',
                'name' => [
                    'lt' => 'Mažmeniniai klientai',
                    'en' => 'Retail Customers',
                ],
                'description' => [
                    'lt' => 'Standartinė grupė visiems naujiems klientams su baziniais pasiūlymais.',
                    'en' => 'Default group for new customers with baseline offers.',
                ],
                'discount_percentage' => 0.0,
                'discount_fixed' => 0.0,
                'has_special_pricing' => false,
                'has_volume_discounts' => false,
                'can_place_orders' => true,
                'is_default' => true,
                'metadata' => [
                    'tagline_lt' => 'Greitas aptarnavimas ir aiškios kainos.',
                    'tagline_en' => 'Fast service with transparent pricing.',
                ],
                'conditions' => [
                    'min_yearly_spend_eur' => 0,
                ],
            ],
            [
                'code' => 'VIP',
                'type' => 'vip',
                'name' => [
                    'lt' => 'VIP klientai',
                    'en' => 'VIP Customers',
                ],
                'description' => [
                    'lt' => 'Klientai su dedikuotu vadybininku ir nuolatine 8 % nuolaida.',
                    'en' => 'Customers with a dedicated manager and permanent 8% discount.',
                ],
                'discount_percentage' => 8.0,
                'discount_fixed' => 0.0,
                'has_special_pricing' => true,
                'has_volume_discounts' => true,
                'can_place_orders' => true,
                'is_default' => false,
                'metadata' => [
                    'support_email' => 'vip@statyba.lt',
                    'priority_hours' => '08:00-18:00',
                ],
                'conditions' => [
                    'min_yearly_spend_eur' => 5000,
                    'required_project_manager' => true,
                ],
            ],
            [
                'code' => 'WHOLESALE',
                'type' => 'wholesale',
                'name' => [
                    'lt' => 'Didmeniniai partneriai',
                    'en' => 'Wholesale Partners',
                ],
                'description' => [
                    'lt' => 'Didelių pirkimų partneriai su individualiomis sandėlio kainomis.',
                    'en' => 'Large volume partners with bespoke warehouse pricing.',
                ],
                'discount_percentage' => 12.5,
                'discount_fixed' => 35.0,
                'has_special_pricing' => true,
                'has_volume_discounts' => true,
                'can_place_orders' => true,
                'is_default' => false,
                'metadata' => [
                    'minimum_order_qty' => 10,
                    'payment_terms_days' => 30,
                ],
                'conditions' => [
                    'min_yearly_spend_eur' => 15000,
                    'requires_company_code' => true,
                ],
            ],
            [
                'code' => 'CONSTRUCTION',
                'type' => 'corporate',
                'name' => [
                    'lt' => 'Statybų įmonės',
                    'en' => 'Construction Companies',
                ],
                'description' => [
                    'lt' => 'Profesionalūs rangovai su kreditavimo ir projekto aptarnavimo galimybėmis.',
                    'en' => 'Professional contractors with credit and project servicing options.',
                ],
                'discount_percentage' => 5.0,
                'discount_fixed' => 20.0,
                'has_special_pricing' => true,
                'has_volume_discounts' => false,
                'can_place_orders' => true,
                'is_default' => false,
                'metadata' => [
                    'credit_limit_eur' => 10000,
                    'dedicated_consultant' => true,
                ],
                'conditions' => [
                    'requires_signed_contract' => true,
                    'project_portfolio_required' => true,
                ],
            ],
        ];

        foreach ($groups as $index => $group) {
            $attributes = [
                'name' => $group['name'],
                'slug' => Str::slug($group['name']['lt']),
                'description' => $group['description'],
                'discount_percentage' => $group['discount_percentage'],
                'discount_fixed' => $group['discount_fixed'],
                'has_special_pricing' => $group['has_special_pricing'],
                'has_volume_discounts' => $group['has_volume_discounts'],
                'can_view_prices' => true,
                'can_place_orders' => $group['can_place_orders'],
                'can_view_catalog' => true,
                'can_use_coupons' => true,
                'is_enabled' => true,
                'is_active' => true,
                'is_default' => $group['is_default'],
                'type' => $group['type'],
                'sort_order' => $index + 1,
                'metadata' => $group['metadata'],
                'conditions' => $group['conditions'],
            ];

            /** @var CustomerGroup $customerGroup */
            $customerGroup = CustomerGroup::withTrashed()->updateOrCreate(
                ['code' => $group['code']],
                $attributes,
            );

            if ($customerGroup->trashed()) {
                $customerGroup->restore();
            }

            if ($group['is_default']) {
                CustomerGroup::withTrashed()
                    ->where('id', '!=', $customerGroup->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        }
    }
}
