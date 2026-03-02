<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Company;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\CustomerGroup;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class UsersCompanyTabSeeder extends BaseSeeder
{
    private const COMPANY_USER_COUNT = 12;

    public function run(): void
    {
        if (Company::query()->withoutGlobalScopes()->count() === 0) {
            $this->call(CompanySeeder::class);
        }

        $companies = Company::query()->withoutGlobalScopes()->orderBy('id')->get();
        if ($companies->isEmpty()) {
            $companies = Company::factory()->count(6)->create();
        }

        $customerGroups = $this->ensureCustomerGroups();
        $partners = $this->ensurePartners();
        $products = $this->ensureProducts();
        $coupon = $this->ensureCoupon();
        $discount = $this->ensureDiscount();
        $discountCode = $this->ensureDiscountCode($discount);

        for ($index = 1; $index <= self::COMPANY_USER_COUNT; $index++) {
            /** @var Company $company */
            $company = $companies[($index - 1) % $companies->count()];
            $user = $this->upsertCompanyUser($index, $company);

            $this->upsertUserRelations(
                user: $user,
                company: $company,
                customerGroups: $customerGroups,
                partners: $partners,
                products: $products,
                coupon: $coupon,
                discount: $discount,
                discountCode: $discountCode,
                index: $index,
            );
        }

        $this->command?->info('UsersCompanyTabSeeder: company tab users and all user relations were seeded.');
    }

    /**
     * @return Collection<int, CustomerGroup>
     */
    private function ensureCustomerGroups(): Collection
    {
        $groups = CustomerGroup::query()->withoutGlobalScopes()->orderBy('id')->get();

        if ($groups->isEmpty()) {
            $groups = CustomerGroup::factory()->count(4)->create();
        }

        return $groups;
    }

    /**
     * @return Collection<int, Partner>
     */
    private function ensurePartners(): Collection
    {
        $partners = Partner::query()->withoutGlobalScopes()->orderBy('id')->get();

        if ($partners->isEmpty()) {
            $partners = Partner::factory()->count(4)->create();
        }

        return $partners;
    }

    /**
     * @return Collection<int, Product>
     */
    private function ensureProducts(): Collection
    {
        $products = Product::query()->withoutGlobalScopes()->orderBy('id')->limit(12)->get();

        if ($products->isEmpty()) {
            $products = Product::factory()->count(6)->create();
        }

        return $products;
    }

    private function ensureCoupon(): Coupon
    {
        $coupon = Coupon::query()->withoutGlobalScopes()->where('code', 'COMPANYTAB10')->first();

        if ($coupon instanceof Coupon) {
            return $coupon;
        }

        return Coupon::factory()->active()->create([
            'code'      => 'COMPANYTAB10',
            'name'      => 'Company tab 10%',
            'type'      => 'percentage',
            'value'     => 10.0,
            'is_active' => true,
        ]);
    }

    private function ensureDiscount(): Discount
    {
        $discount = Discount::query()->withoutGlobalScopes()->where('slug', 'company-tab-discount')->first();

        if ($discount instanceof Discount) {
            return $discount;
        }

        return Discount::factory()->active()->create([
            'slug'       => 'company-tab-discount',
            'name'       => 'Company Tab Discount',
            'status'     => 'active',
            'is_active'  => true,
            'is_enabled' => true,
        ]);
    }

    private function ensureDiscountCode(Discount $discount): DiscountCode
    {
        $discountCode = DiscountCode::query()->withoutGlobalScopes()->where('code', 'COMPANYTAB')->first();

        if ($discountCode instanceof DiscountCode) {
            return $discountCode;
        }

        return DiscountCode::factory()
            ->active()
            ->withDiscount($discount)
            ->create([
                'code'        => 'COMPANYTAB',
                'status'      => 'active',
                'is_active'   => true,
                'valid_from'  => now()->subDay(),
                'valid_until' => now()->addMonths(6),
            ]);
    }

    private function upsertCompanyUser(int $index, Company $company): User
    {
        $email = sprintf('company.relations.%02d@example.test', $index);

        $user = User::query()->withoutGlobalScopes()->firstOrNew([
            'email' => $email,
        ]);

        $user->fill([
            'name'              => sprintf('Company Relations %02d', $index),
            'first_name'        => 'Company',
            'last_name'         => sprintf('Relations %02d', $index),
            'account_type'      => 'company',
            'company_id'        => (int) $company->getKey(),
            'company'           => (string) $company->name,
            'job_title'         => 'Project Manager',
            'preferred_locale'  => 'lt',
            'phone_number'      => sprintf('+370600%04d', 1000 + $index),
            'is_active'         => true,
            'is_admin'          => false,
            'password'          => 'Admin123!',
            'email_verified_at' => now(),
        ]);

        $user->save();

        return $user;
    }

    private function upsertUserRelations(
        User $user,
        Company $company,
        Collection $customerGroups,
        Collection $partners,
        Collection $products,
        Coupon $coupon,
        Discount $discount,
        DiscountCode $discountCode,
        int $index,
    ): void {
        /** @var CustomerGroup $group */
        $group = $customerGroups[($index - 1) % $customerGroups->count()];
        $user->customerGroups()->syncWithoutDetaching([
            (int) $group->getKey() => ['assigned_at' => now()],
        ]);

        /** @var Partner $partner */
        $partner = $partners[($index - 1) % $partners->count()];
        $user->partners()->syncWithoutDetaching([(int) $partner->getKey()]);

        Address::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'user_id'        => $user->getKey(),
                'type'           => AddressType::SHIPPING->value,
                'address_line_1' => sprintf('Company street %d', $index),
            ],
            [
                'first_name'   => (string) ($user->first_name ?? 'Company'),
                'last_name'    => (string) ($user->last_name ?? 'User'),
                'city'         => 'Vilnius',
                'postal_code'  => sprintf('01%03d', $index),
                'country_code' => 'LT',
                'phone'        => (string) ($user->phone_number ?? ''),
                'is_default'   => true,
                'is_shipping'  => true,
                'is_billing'   => false,
                'is_active'    => true,
                'company_name' => (string) $company->name,
            ],
        );

        Address::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'user_id'        => $user->getKey(),
                'type'           => AddressType::BILLING->value,
                'address_line_1' => sprintf('Billing avenue %d', $index),
            ],
            [
                'first_name'   => (string) ($user->first_name ?? 'Company'),
                'last_name'    => (string) ($user->last_name ?? 'User'),
                'city'         => 'Kaunas',
                'postal_code'  => sprintf('44%03d', $index),
                'country_code' => 'LT',
                'phone'        => (string) ($user->phone_number ?? ''),
                'is_default'   => false,
                'is_shipping'  => false,
                'is_billing'   => true,
                'is_active'    => true,
                'company_name' => (string) $company->name,
            ],
        );

        $orderNumber = sprintf('CMP-%05d', $index);
        $order = Order::query()->withoutGlobalScopes()->where('number', $orderNumber)->first();

        if (! $order instanceof Order) {
            $order = Order::factory()->create([
                'number'  => $orderNumber,
                'user_id' => $user->getKey(),
                'status'  => 'processing',
            ]);
        } else {
            $order->user_id = $user->getKey();
            $order->status = 'processing';
            $order->save();
        }

        /** @var Product $product */
        $product = $products[($index - 1) % $products->count()];
        $unitPrice = is_numeric($product->price) ? (float) $product->price : 25.0;

        CartItem::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'user_id'    => $user->getKey(),
                'product_id' => $product->getKey(),
                'session_id' => 'company-tab-' . $user->getKey(),
            ],
            [
                'quantity'         => 2,
                'minimum_quantity' => 1,
                'unit_price'       => $unitPrice,
                'price'            => $unitPrice,
                'discount_amount'  => 0.0,
                'total_price'      => round($unitPrice * 2, 2),
                'product_snapshot' => [
                    'name'  => (string) $product->name,
                    'sku'   => (string) $product->sku,
                    'price' => $unitPrice,
                ],
            ],
        );

        CouponUsage::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'user_id'   => $user->getKey(),
                'coupon_id' => $coupon->getKey(),
                'order_id'  => $order->getKey(),
            ],
            [
                'discount_amount' => 10.0,
                'used_at'         => now(),
                'metadata'        => ['source' => 'users_company_tab_seed'],
            ],
        );

        DiscountRedemption::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'user_id'     => $user->getKey(),
                'discount_id' => $discount->getKey(),
                'order_id'    => $order->getKey(),
            ],
            [
                'code_id'       => $discountCode->getKey(),
                'amount_saved'  => 8.5,
                'currency_code' => 'EUR',
                'status'        => 'redeemed',
                'redeemed_at'   => now(),
                'created_by'    => $user->getKey(),
                'updated_by'    => $user->getKey(),
                'metadata'      => ['source' => 'users_company_tab_seed'],
            ],
        );

        $notificationType = 'App\\Notifications\\CompanyTabSeededNotification';
        $notification = Notification::query()->withoutGlobalScopes()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->getKey())
            ->where('type', $notificationType)
            ->first();

        if (! $notification instanceof Notification) {
            Notification::query()->withoutGlobalScopes()->create([
                'id'              => (string) Str::uuid(),
                'type'            => $notificationType,
                'notifiable_type' => User::class,
                'notifiable_id'   => $user->getKey(),
                'user_id'         => $user->getKey(),
                'data'            => [
                    'title'   => 'Company relation seed',
                    'message' => 'Seeded all user relations for company-tab interface.',
                    'source'  => 'users_company_tab_seed',
                ],
            ]);
        } else {
            $notification->update([
                'user_id' => $user->getKey(),
                'data'    => [
                    'title'   => 'Company relation seed',
                    'message' => 'Seeded all user relations for company-tab interface.',
                    'source'  => 'users_company_tab_seed',
                ],
                'read_at' => null,
            ]);
        }

        $referralCodeValue = sprintf('CMPREF%02d', $index);
        $referralCode = ReferralCode::query()->withoutGlobalScopes()->updateOrCreate(
            ['code' => $referralCodeValue],
            [
                'user_id'       => $user->getKey(),
                'is_active'     => true,
                'usage_limit'   => 50,
                'usage_count'   => 0,
                'reward_amount' => 15.0,
                'reward_type'   => 'credit',
                'expires_at'    => now()->addMonths(6),
                'metadata'      => ['source' => 'users_company_tab_seed'],
            ],
        );

        $referredUser = $this->upsertReferredUser($index);

        $referral = Referral::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'referrer_id' => $user->getKey(),
                'referred_id' => $referredUser->getKey(),
            ],
            [
                'referral_code' => $referralCode->code,
                'status'        => 'completed',
                'completed_at'  => now()->subDay(),
                'expires_at'    => now()->addMonths(6),
                'source'        => 'admin',
                'campaign'      => 'company-tab',
                'title'         => [
                    'lt' => 'Įmonės nukreipimo įrašas',
                    'en' => 'Company referral entry',
                ],
                'description' => [
                    'lt' => 'Sukurta per UsersCompanyTabSeeder.',
                    'en' => 'Created by UsersCompanyTabSeeder.',
                ],
            ],
        );

        ReferralReward::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'user_id'     => $user->getKey(),
                'referral_id' => $referral->getKey(),
                'type'        => 'referrer_bonus',
            ],
            [
                'title' => [
                    'lt' => sprintf('Rekomendacijos premija %02d', $index),
                    'en' => sprintf('Referral reward %02d', $index),
                ],
                'description' => [
                    'lt' => 'Premija už nukreiptą klientą (seed data).',
                    'en' => 'Reward for a referred customer (seed data).',
                ],
                'amount'        => 20.0,
                'currency_code' => 'EUR',
                'status'        => 'completed',
                'is_active'     => true,
                'priority'      => 10,
                'expires_at'    => now()->addMonths(6),
                'reward_data'   => ['category' => 'credit'],
            ],
        );

        Subscriber::query()->withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $user->getKey()],
            [
                'email'                   => (string) $user->email,
                'first_name'              => (string) ($user->first_name ?? 'Company'),
                'last_name'               => (string) ($user->last_name ?? 'User'),
                'company'                 => (string) $company->name,
                'status'                  => 'active',
                'newsletter_subscription' => true,
                'subscribed_at'           => now()->subDays($index),
            ],
        );
    }

    private function upsertReferredUser(int $index): User
    {
        $email = sprintf('company.referred.%02d@example.test', $index);

        $user = User::query()->withoutGlobalScopes()->firstOrNew([
            'email' => $email,
        ]);

        $user->fill([
            'name'              => sprintf('Referred User %02d', $index),
            'first_name'        => 'Referred',
            'last_name'         => sprintf('User %02d', $index),
            'account_type'      => 'private',
            'preferred_locale'  => 'lt',
            'is_active'         => true,
            'is_admin'          => false,
            'password'          => 'Admin123!',
            'email_verified_at' => now(),
        ]);

        $user->save();

        return $user;
    }
}
