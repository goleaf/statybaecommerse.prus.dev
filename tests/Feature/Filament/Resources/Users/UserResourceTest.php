<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\Users;

use App\Enums\Industry;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\Users\RelationManagers\CartItemsRelationManager;
use App\Filament\Resources\Users\RelationManagers\CouponUsagesRelationManager;
use App\Filament\Resources\Users\RelationManagers\CustomerGroupsRelationManager;
use App\Filament\Resources\Users\RelationManagers\DiscountRedemptionsRelationManager;
use App\Filament\Resources\Users\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Users\RelationManagers\NotificationsRelationManager;
use App\Filament\Resources\Users\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\Users\RelationManagers\PartnersRelationManager;
use App\Filament\Resources\Users\RelationManagers\ReferralCodesRelationManager;
use App\Filament\Resources\Users\RelationManagers\ReferralRewardsRelationManager;
use App\Filament\Resources\Users\RelationManagers\ReferralsRelationManager;
use App\Filament\Resources\Users\RelationManagers\SubscriberRelationManager;
use App\Models\Address;
use App\Models\AdminUser;
use App\Models\CartItem;
use App\Models\Company;
use App\Models\CouponUsage;
use App\Models\CustomerGroup;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerTier;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\Subscriber;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_can_list_users_and_sort_by_columns(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $component = Livewire::test(ListUsers::class)
            ->assertSuccessful()
            ->assertTableColumnExists('name')
            ->assertTableColumnExists('email')
            ->assertTableColumnExists('phone_number')
            ->assertTableColumnExists('is_active')
            ->assertTableColumnExists('created_at')
            ->assertTableActionExists('view');

        $table = $component->instance()->getTable();

        $this->assertTrue($table->getColumn('name')->isSortable());
        $this->assertTrue($table->getColumn('email')->isSortable());
        $this->assertTrue($table->getColumn('phone_number')->isSortable());
        $this->assertTrue($table->getColumn('is_active')->isSortable());
        $this->assertTrue($table->getColumn('created_at')->isSortable());
    }

    public function test_users_table_record_url_defaults_to_edit_page(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();

        $component = Livewire::test(ListUsers::class)
            ->assertSuccessful();

        $table = $component->instance()->getTable();

        $this->assertSame(
            UserResource::getUrl('edit', ['record' => $user]),
            $table->getRecordUrl($user),
        );
    }

    public function test_admins_are_hidden_from_list(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $adminUser = User::factory()->admin()->create();

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords([$user])
            ->assertCanNotSeeTableRecords([$adminUser]);
    }

    public function test_users_list_page_has_tabs_for_all_related_information(): void
    {
        $tabs = app(ListUsers::class)->getTabs();

        $this->assertSame([
            'all',
            'company',
            'addresses',
            'cart_items',
            'customer_groups',
            'partners',
            'referral_codes',
            'referrals',
            'referral_rewards',
            'documents',
            'coupon_usages',
            'discount_redemptions',
            'notifications',
            'subscriber',
        ], array_keys($tabs));
    }

    public function test_can_list_users_with_all_related_information_table_columns(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        Livewire::test(ListUsers::class)
            ->assertSuccessful()
            ->assertTableColumnExists('addresses_count')
            ->assertTableColumnExists('cart_items_count')
            ->assertTableColumnExists('customer_groups_count')
            ->assertTableColumnExists('partners_count')
            ->assertTableColumnExists('referral_codes_count')
            ->assertTableColumnExists('referrals_count')
            ->assertTableColumnExists('referral_rewards_count')
            ->assertTableColumnExists('documents_count')
            ->assertTableColumnExists('coupon_usages_count')
            ->assertTableColumnExists('discount_redemptions_count')
            ->assertTableColumnExists('notifications_count')
            ->assertTableColumnExists('subscriber_count');
    }

    public function test_related_information_tabs_filter_users_with_each_relation(): void
    {
        $company = Company::query()->create([
            'name'      => 'Related Tabs Company',
            'is_active' => true,
        ]);

        $relatedUser = User::factory()->create([
            'company_id' => $company->getKey(),
            'is_active'  => true,
        ]);
        $plainUser = User::factory()->create([
            'company_id' => null,
            'is_active'  => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => $relatedUser->getKey(),
        ]);

        Address::factory()->create([
            'user_id' => $relatedUser->getKey(),
        ]);
        CartItem::factory()->create([
            'user_id' => $relatedUser->getKey(),
        ]);

        $customerGroup = CustomerGroup::factory()->create();
        $relatedUser->customerGroups()->attach($customerGroup->getKey());

        $partner = Partner::factory()->create();
        $relatedUser->partners()->attach($partner->getKey());

        ReferralCode::factory()->create([
            'user_id' => $relatedUser->getKey(),
        ]);

        $referral = Referral::query()->create([
            'referrer_id'   => $relatedUser->getKey(),
            'referred_id'   => $plainUser->getKey(),
            'referral_code' => 'REF-' . strtoupper(substr(md5((string) microtime(true)), 0, 8)),
            'status'        => 'pending',
        ]);

        ReferralReward::factory()->create([
            'user_id'     => $relatedUser->getKey(),
            'referral_id' => $referral->getKey(),
        ]);

        Document::factory()->create([
            'documentable_type' => User::class,
            'documentable_id'   => $relatedUser->getKey(),
            'created_by'        => $relatedUser->getKey(),
            'updated_by'        => $relatedUser->getKey(),
        ]);

        CouponUsage::factory()->create([
            'user_id'  => $relatedUser->getKey(),
            'order_id' => $order->getKey(),
        ]);

        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create([
            'discount_id' => $discount->getKey(),
            'created_by'  => $relatedUser->getKey(),
            'updated_by'  => $relatedUser->getKey(),
        ]);

        DiscountRedemption::factory()->create([
            'discount_id' => $discount->getKey(),
            'code_id'     => $discountCode->getKey(),
            'user_id'     => $relatedUser->getKey(),
            'order_id'    => $order->getKey(),
            'created_by'  => $relatedUser->getKey(),
            'updated_by'  => $relatedUser->getKey(),
        ]);

        Notification::factory()->create([
            'notifiable_type' => User::class,
            'notifiable_id'   => $relatedUser->getKey(),
        ]);

        Subscriber::factory()->create([
            'user_id' => $relatedUser->getKey(),
            'email'   => $relatedUser->email,
        ]);

        $tabs = app(ListUsers::class)->getTabs();

        $this->assertSame(2, $tabs['all']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['company']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['addresses']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['cart_items']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['customer_groups']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['partners']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['referral_codes']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['referrals']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['referral_rewards']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['documents']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['coupon_usages']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['discount_redemptions']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['notifications']->modifyQuery(User::query())->count());
        $this->assertSame(1, $tabs['subscriber']->modifyQuery(User::query())->count());
    }

    public function test_create_user_handles_empty_related_option_labels_without_server_error(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $company = Company::query()->create([
            'name'      => '',
            'is_active' => true,
        ]);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'account_type' => 'company',
            ])
            ->fillForm([
                'company_id' => $company->getKey(),
            ])
            ->assertHasNoFormErrors()
            ->assertSuccessful();
    }

    public function test_create_user_can_create_company_from_company_tab_with_all_fields(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $email = 'inline-company-user-' . uniqid('', true) . '@example.com';
        $industry = Industry::cases()[0]->value;

        Livewire::test(CreateUser::class)
            ->assertFormFieldVisible('company_id')
            ->assertFormComponentActionExists('company_id', 'createOption')
            ->fillForm([
                'account_type' => 'company',
                'email'        => $email,
                'password'     => 'SecurePassword123!',
            ])
            ->mountFormComponentAction('company_id', 'createOption')
            ->setFormComponentActionData([
                'name'            => 'Inline Company',
                'email'           => 'contact@inline-company.test',
                'phone'           => '+37060000001',
                'website'         => 'https://inline-company.test',
                'address'         => 'Inline street 10, Vilnius',
                'industry'        => $industry,
                'size'            => 'medium',
                'description'     => 'Inline created company',
                'metadata.source' => 'users_create_tab',
                'metadata.team'   => 'sales',
                'is_active'       => true,
            ])
            ->callMountedFormComponentAction()
            ->assertHasNoFormErrors()
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', $email)->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->company_id);

        $company = Company::query()->find($user->company_id);

        $this->assertNotNull($company);
        $this->assertSame('Inline Company', $company->name);
        $this->assertSame('contact@inline-company.test', $company->email);
        $this->assertSame('+37060000001', $company->phone);
        $this->assertSame('https://inline-company.test', $company->website);
        $this->assertSame('Inline street 10, Vilnius', $company->address);
        $this->assertSame($industry, $company->getRawOriginal('industry'));
        $this->assertSame('medium', $company->size);
        $this->assertSame('Inline created company', $company->description);
        $this->assertSame([
            'source' => 'users_create_tab',
            'team'   => 'sales',
        ], $company->metadata);
        $this->assertTrue((bool) $company->is_active);
    }

    public function test_create_user_can_assign_relation_tab_models(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $company = Company::query()->create([
            'name'      => 'Relations Company',
            'is_active' => true,
        ]);
        $customerGroup = CustomerGroup::factory()->create();
        $partner = Partner::factory()->create();

        $email = 'relations-user-' . uniqid('', true) . '@example.com';

        Livewire::test(CreateUser::class)
            ->assertFormFieldExists('company_id')
            ->assertFormFieldExists('customer_group_ids')
            ->assertFormFieldExists('partner_ids')
            ->assertFormFieldExists('addresses')
            ->fillForm([
                'account_type'       => 'company',
                'company_id'         => $company->getKey(),
                'email'              => $email,
                'password'           => 'SecurePassword123!',
                'customer_group_ids' => [$customerGroup->getKey()],
                'partner_ids'        => [$partner->getKey()],
                'addresses'          => [
                    [
                        'type'           => 'shipping',
                        'first_name'     => 'Jane',
                        'last_name'      => 'Doe',
                        'address_line_1' => 'Main street 1',
                        'city'           => 'Vilnius',
                        'postal_code'    => '01100',
                        'country_code'   => 'LT',
                        'is_active'      => true,
                    ],
                    [
                        'type'           => 'billing',
                        'first_name'     => 'Jane',
                        'last_name'      => 'Doe',
                        'address_line_1' => 'Business street 9',
                        'city'           => 'Kaunas',
                        'postal_code'    => '44249',
                        'country_code'   => 'LT',
                        'is_active'      => true,
                    ],
                    [
                        'type'           => 'shipping',
                        'first_name'     => 'John',
                        'last_name'      => 'Smith',
                        'address_line_1' => 'Park avenue 12',
                        'city'           => 'Klaipeda',
                        'postal_code'    => '91234',
                        'country_code'   => 'LT',
                        'is_active'      => true,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', $email)->first();

        $this->assertNotNull($user);
        $this->assertSame($company->getKey(), $user->company_id);

        $this->assertDatabaseHas('customer_group_user', [
            'user_id'           => $user->getKey(),
            'customer_group_id' => $customerGroup->getKey(),
        ]);

        $this->assertDatabaseHas('partner_users', [
            'user_id'    => $user->getKey(),
            'partner_id' => $partner->getKey(),
        ]);

        $this->assertDatabaseHas('addresses', [
            'user_id'        => $user->getKey(),
            'first_name'     => 'Jane',
            'last_name'      => 'Doe',
            'address_line_1' => 'Main street 1',
            'city'           => 'Vilnius',
            'postal_code'    => '01100',
            'country_code'   => 'LT',
        ]);

        $this->assertDatabaseHas('addresses', [
            'user_id'        => $user->getKey(),
            'first_name'     => 'Jane',
            'last_name'      => 'Doe',
            'address_line_1' => 'Business street 9',
            'city'           => 'Kaunas',
            'postal_code'    => '44249',
            'country_code'   => 'LT',
        ]);

        $this->assertDatabaseHas('addresses', [
            'user_id'        => $user->getKey(),
            'first_name'     => 'John',
            'last_name'      => 'Smith',
            'address_line_1' => 'Park avenue 12',
            'city'           => 'Klaipeda',
            'postal_code'    => '91234',
            'country_code'   => 'LT',
        ]);

        $this->assertSame(3, $user->fresh()->addresses()->count());
    }

    public function test_customer_groups_relation_manager_can_attach_existing_customer_group(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $customerGroup = CustomerGroup::factory()->create([
            'name' => [
                'lt' => 'Wholesale Plus',
                'en' => 'Wholesale Plus',
            ],
        ]);

        Livewire::test(CustomerGroupsRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('attach')
            ->set('mountedActions.0.data.recordId', $customerGroup->getKey())
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('customer_group_user', [
            'user_id'           => $user->getKey(),
            'customer_group_id' => $customerGroup->getKey(),
        ]);
    }

    public function test_customer_groups_relation_manager_can_create_customer_group_with_all_fields_and_attach_to_user(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $code = 'CG-' . strtoupper(substr(md5((string) microtime(true)), 0, 8));

        Livewire::test(CustomerGroupsRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.name', 'Enterprise Group')
            ->set('mountedActions.0.data.code', $code)
            ->set('mountedActions.0.data.type', 'b2b')
            ->set('mountedActions.0.data.color', '#112233')
            ->set('mountedActions.0.data.icon', 'heroicon-o-user-group')
            ->set('mountedActions.0.data.description', 'Enterprise customer segment')
            ->set('mountedActions.0.data.discount_percentage', 17.5)
            ->set('mountedActions.0.data.discount_fixed', 120)
            ->set('mountedActions.0.data.has_special_pricing', true)
            ->set('mountedActions.0.data.has_volume_discounts', true)
            ->set('mountedActions.0.data.can_view_prices', true)
            ->set('mountedActions.0.data.can_place_orders', true)
            ->set('mountedActions.0.data.can_view_catalog', false)
            ->set('mountedActions.0.data.can_use_coupons', false)
            ->set('mountedActions.0.data.minimum_order_amount', 250)
            ->set('mountedActions.0.data.credit_limit', 5000)
            ->set('mountedActions.0.data.payment_terms', 'net_60')
            ->set('mountedActions.0.data.sort_order', 30)
            ->set('mountedActions.0.data.metadata.source', 'users_customer_groups_tab')
            ->set('mountedActions.0.data.metadata.segment', 'enterprise')
            ->set('mountedActions.0.data.conditions.min_orders', '5')
            ->set('mountedActions.0.data.conditions.region', 'EU')
            ->set('mountedActions.0.data.is_active', true)
            ->set('mountedActions.0.data.is_enabled', true)
            ->set('mountedActions.0.data.is_default', false)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var CustomerGroup|null $group */
        $group = CustomerGroup::query()->where('code', $code)->first();

        $this->assertNotNull($group);
        $this->assertSame('Enterprise Group', $group->getTranslation('name', 'lt'));
        $this->assertSame('Enterprise Group', $group->getTranslation('name', 'en'));
        $this->assertSame('b2b', $group->type);
        $this->assertSame('#112233', $group->color);
        $this->assertSame('heroicon-o-user-group', $group->icon);
        $this->assertSame('Enterprise customer segment', $group->getTranslation('description', 'lt'));
        $this->assertSame(17.5, (float) $group->discount_percentage);
        $this->assertSame(120.0, (float) $group->discount_fixed);
        $this->assertTrue((bool) $group->has_special_pricing);
        $this->assertTrue((bool) $group->has_volume_discounts);
        $this->assertTrue((bool) $group->can_view_prices);
        $this->assertTrue((bool) $group->can_place_orders);
        $this->assertFalse((bool) $group->can_view_catalog);
        $this->assertFalse((bool) $group->can_use_coupons);
        $this->assertSame(250.0, (float) $group->minimum_order_amount);
        $this->assertSame(5000.0, (float) $group->credit_limit);
        $this->assertSame('net_60', $group->payment_terms);
        $this->assertSame(30, (int) $group->sort_order);
        $this->assertSame([
            'source'  => 'users_customer_groups_tab',
            'segment' => 'enterprise',
        ], $group->metadata);
        $this->assertSame([
            'min_orders' => '5',
            'region'     => 'EU',
        ], $group->conditions);
        $this->assertTrue((bool) $group->is_active);
        $this->assertTrue((bool) $group->is_enabled);
        $this->assertFalse((bool) $group->is_default);

        $this->assertDatabaseHas('customer_group_user', [
            'user_id'           => $user->getKey(),
            'customer_group_id' => $group->getKey(),
        ]);
    }

    public function test_customer_groups_relation_manager_defaults_financial_fields_when_left_empty(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $code = 'CG-EMPTY-' . strtoupper(substr(md5((string) microtime(true)), 0, 6));

        Livewire::test(CustomerGroupsRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.name', 'Default Financial Group')
            ->set('mountedActions.0.data.code', $code)
            ->set('mountedActions.0.data.type', 'retail')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var CustomerGroup|null $group */
        $group = CustomerGroup::query()->where('code', $code)->first();

        $this->assertNotNull($group);
        $this->assertSame(0.0, (float) $group->discount_percentage);
        $this->assertSame(0.0, (float) $group->discount_fixed);
        $this->assertSame(0.0, (float) $group->minimum_order_amount);
        $this->assertSame(0.0, (float) $group->credit_limit);

        $this->assertDatabaseHas('customer_group_user', [
            'user_id'           => $user->getKey(),
            'customer_group_id' => $group->getKey(),
        ]);
    }

    public function test_orders_relation_manager_rejects_invalid_status_without_server_error(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();

        Livewire::test(OrdersRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.status', '123')
            ->set('mountedActions.0.data.total', 42.5)
            ->callMountedTableAction()
            ->assertHasTableActionErrors();

        $this->assertSame(0, $user->orders()->count());
    }

    public function test_orders_relation_manager_can_create_order_with_safe_defaults(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();

        Livewire::test(OrdersRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.status', 'pending')
            ->set('mountedActions.0.data.total', 99.99)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var Order|null $order */
        $order = Order::query()
            ->where('user_id', $user->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($order);
        $this->assertSame('pending', (string) $order->getRawOriginal('status'));
        $this->assertSame('pending', (string) $order->getRawOriginal('payment_status'));
        $this->assertSame('created', (string) $order->getRawOriginal('payment_state'));
        $this->assertSame('EUR', (string) $order->currency);
        $this->assertSame(0.0, (float) $order->subtotal);
        $this->assertSame(0.0, (float) $order->tax_amount);
        $this->assertSame(0.0, (float) $order->shipping_amount);
        $this->assertSame(0.0, (float) $order->discount_amount);
        $this->assertSame(99.99, (float) $order->total);
    }

    public function test_addresses_relation_manager_can_create_address_for_owner_user(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();

        Livewire::test(AddressesRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.type', 'shipping')
            ->set('mountedActions.0.data.first_name', 'Jonas')
            ->set('mountedActions.0.data.last_name', 'Jonaitis')
            ->set('mountedActions.0.data.address_line_1', 'Gedimino pr. 1')
            ->set('mountedActions.0.data.city', 'Vilnius')
            ->set('mountedActions.0.data.postal_code', '01103')
            ->set('mountedActions.0.data.country_code', 'lt')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var Address|null $address */
        $address = Address::query()
            ->where('user_id', $user->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($address);
        $this->assertSame('shipping', $address->type);
        $this->assertSame('LT', $address->country_code);
        $this->assertTrue((bool) $address->is_active);
    }

    public function test_addresses_relation_manager_applies_default_type_and_country(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();

        Livewire::test(AddressesRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.first_name', 'Petras')
            ->set('mountedActions.0.data.last_name', 'Petraitis')
            ->set('mountedActions.0.data.address_line_1', 'Vilniaus g. 10')
            ->set('mountedActions.0.data.city', 'Kaunas')
            ->set('mountedActions.0.data.postal_code', '44280')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var Address|null $address */
        $address = Address::query()
            ->where('user_id', $user->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($address);
        $this->assertSame('shipping', $address->type);
        $this->assertSame('LT', $address->country_code);
    }

    public function test_partners_relation_manager_can_attach_existing_partner_from_list(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $partner = Partner::factory()->create([
            'name'       => 'Attach Partner',
            'code'       => 'PARTNER-' . strtoupper(substr(md5((string) microtime(true)), 0, 6)),
            'is_enabled' => true,
        ]);

        Livewire::test(PartnersRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('attach')
            ->set('mountedActions.0.data.recordId', $partner->getKey())
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('partner_users', [
            'user_id'    => $user->getKey(),
            'partner_id' => $partner->getKey(),
        ]);
    }

    public function test_partners_relation_manager_can_create_partner_with_all_fields_and_attach_to_user(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $tier = PartnerTier::factory()->create([
            'name'     => 'Growth',
            'priority' => 10,
        ]);

        $code = 'P-' . strtoupper(substr(md5((string) microtime(true)), 0, 10));
        $email = 'partners-tab-' . uniqid('', true) . '@example.com';

        Livewire::test(PartnersRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.name', 'Enterprise Partner')
            ->set('mountedActions.0.data.code', $code)
            ->set('mountedActions.0.data.contact_email', $email)
            ->set('mountedActions.0.data.contact_phone', '+37060001111')
            ->set('mountedActions.0.data.discount_rate', 12.5)
            ->set('mountedActions.0.data.commission_rate', 4.25)
            ->set('mountedActions.0.data.tier_id', $tier->getKey())
            ->set('mountedActions.0.data.metadata.source', 'users_partners_tab')
            ->set('mountedActions.0.data.metadata.channel', 'b2b')
            ->set('mountedActions.0.data.is_enabled', true)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $partner = Partner::query()->where('code', $code)->first();

        $this->assertNotNull($partner);
        $this->assertSame('Enterprise Partner', $partner->name);
        $this->assertSame($email, $partner->contact_email);
        $this->assertSame('+37060001111', $partner->contact_phone);
        $this->assertSame(12.5, (float) $partner->discount_rate);
        $this->assertSame(4.25, (float) $partner->commission_rate);
        $this->assertSame($tier->getKey(), $partner->tier_id);
        $this->assertSame([
            'source'  => 'users_partners_tab',
            'channel' => 'b2b',
        ], $partner->metadata);
        $this->assertTrue((bool) $partner->is_enabled);

        $this->assertDatabaseHas('partner_users', [
            'user_id'    => $user->getKey(),
            'partner_id' => $partner->getKey(),
        ]);
    }

    public function test_partners_relation_manager_can_edit_attached_partner_with_all_fields(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $initialTier = PartnerTier::factory()->create(['name' => 'Base', 'priority' => 20]);
        $updatedTier = PartnerTier::factory()->create(['name' => 'Premium', 'priority' => 30]);

        $partner = Partner::factory()->create([
            'name'            => 'Original Partner',
            'code'            => 'ORI-' . strtoupper(substr(md5((string) microtime(true)), 0, 7)),
            'contact_email'   => 'original-partner@example.com',
            'contact_phone'   => '+37060002222',
            'discount_rate'   => 5.5,
            'commission_rate' => 2.2,
            'tier_id'         => $initialTier->getKey(),
            'metadata'        => ['source' => 'old'],
            'is_enabled'      => true,
        ]);

        $user->partners()->attach($partner->getKey());

        $updatedCode = 'UPD-' . strtoupper(substr(md5((string) microtime(true)), 0, 7));

        Livewire::test(PartnersRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('edit', $partner)
            ->set('mountedActions.0.data.name', 'Updated Partner')
            ->set('mountedActions.0.data.code', $updatedCode)
            ->set('mountedActions.0.data.contact_email', 'updated-partner@example.com')
            ->set('mountedActions.0.data.contact_phone', '+37060003333')
            ->set('mountedActions.0.data.discount_rate', 18.75)
            ->set('mountedActions.0.data.commission_rate', 7.15)
            ->set('mountedActions.0.data.tier_id', $updatedTier->getKey())
            ->set('mountedActions.0.data.metadata', [
                'source' => 'updated',
                'team'   => 'enterprise',
            ])
            ->set('mountedActions.0.data.is_enabled', false)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $partner->refresh();

        $this->assertSame('Updated Partner', $partner->name);
        $this->assertSame($updatedCode, $partner->code);
        $this->assertSame('updated-partner@example.com', $partner->contact_email);
        $this->assertSame('+37060003333', $partner->contact_phone);
        $this->assertSame(18.75, (float) $partner->discount_rate);
        $this->assertSame(7.15, (float) $partner->commission_rate);
        $this->assertSame($updatedTier->getKey(), $partner->tier_id);
        $this->assertSame([
            'source' => 'updated',
            'team'   => 'enterprise',
        ], $partner->metadata);
        $this->assertFalse((bool) $partner->is_enabled);

        $this->assertDatabaseHas('partner_users', [
            'user_id'    => $user->getKey(),
            'partner_id' => $partner->getKey(),
        ]);
    }

    public function test_referrals_relation_manager_exposes_create_action(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        Livewire::test(ReferralsRelationManager::class, [
            'ownerRecord' => User::factory()->create(),
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->assertTableActionExists('create');
    }

    public function test_referrals_relation_manager_can_create_referral_for_owner_user(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $referrer = User::factory()->create();
        $referred = User::factory()->create();
        $code = 'REL-' . strtoupper(substr(md5((string) microtime(true)), 0, 8));

        Livewire::test(ReferralsRelationManager::class, [
            'ownerRecord' => $referrer,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.referred_id', $referred->getKey())
            ->set('mountedActions.0.data.referral_code', $code)
            ->set('mountedActions.0.data.status', 'pending')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('referrals', [
            'referrer_id'   => $referrer->getKey(),
            'referred_id'   => $referred->getKey(),
            'referral_code' => $code,
            'status'        => 'pending',
        ]);
    }

    public function test_referral_codes_relation_manager_can_create_code_for_owner_user(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $code = 'REF-' . strtoupper(substr(md5((string) microtime(true)), 0, 10));

        Livewire::test(ReferralCodesRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.code', $code)
            ->set('mountedActions.0.data.reward_amount', 19.99)
            ->set('mountedActions.0.data.usage_limit', 5)
            ->set('mountedActions.0.data.is_active', true)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var ReferralCode|null $referralCode */
        $referralCode = ReferralCode::withoutGlobalScopes()
            ->where('user_id', $user->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($referralCode);
        $this->assertSame($code, $referralCode->code);
        $this->assertSame(19.99, (float) $referralCode->reward_amount);
        $this->assertSame(5, (int) $referralCode->usage_limit);
        $this->assertTrue((bool) $referralCode->is_active);
    }

    public function test_referral_codes_relation_manager_generates_code_by_default_when_missing(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();

        Livewire::test(ReferralCodesRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var ReferralCode|null $referralCode */
        $referralCode = ReferralCode::withoutGlobalScopes()
            ->where('user_id', $user->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($referralCode);
        $this->assertNotSame('', trim((string) $referralCode->code));
        $this->assertLessThanOrEqual(20, strlen((string) $referralCode->code));
        $this->assertTrue((bool) $referralCode->is_active);
    }

    public function test_documents_relation_manager_can_create_document_for_user(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $template = DocumentTemplate::factory()->create([
            'name'      => 'User Contract',
            'is_active' => true,
        ]);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.document_template_id', $template->getKey())
            ->set('mountedActions.0.data.title', 'Contract for customer')
            ->set('mountedActions.0.data.status', 'draft')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('documents', [
            'document_template_id' => $template->getKey(),
            'documentable_type'    => User::class,
            'documentable_id'      => $user->getKey(),
            'title'                => 'Contract for customer',
            'status'               => 'draft',
        ]);
    }

    public function test_documents_relation_manager_defaults_status_to_draft_when_not_provided(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $template = DocumentTemplate::factory()->create([
            'name'      => 'Default Status Template',
            'is_active' => true,
        ]);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.document_template_id', $template->getKey())
            ->set('mountedActions.0.data.title', 'Document without explicit status')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('documents', [
            'document_template_id' => $template->getKey(),
            'documentable_type'    => User::class,
            'documentable_id'      => $user->getKey(),
            'title'                => 'Document without explicit status',
            'status'               => Document::STATUS_DRAFT,
        ]);
    }

    public function test_documents_relation_manager_can_create_document_without_selecting_template(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();

        DocumentTemplate::query()->delete();

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.title', 'Fallback template document')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var Document|null $document */
        $document = Document::withoutGlobalScopes()
            ->where('documentable_type', User::class)
            ->where('documentable_id', $user->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($document);
        $this->assertNotNull($document->document_template_id);
        $this->assertDatabaseHas('document_templates', [
            'id' => $document->document_template_id,
        ]);
    }

    public function test_documents_relation_manager_lists_documents_with_legacy_status_values(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $template = DocumentTemplate::factory()->create([
            'name'      => 'Legacy Status Template',
            'is_active' => true,
        ]);

        $legacyDocument = Document::withoutGlobalScopes()->create([
            'document_template_id' => $template->getKey(),
            'title'                => 'Legacy status document',
            'content'              => '<h1>Legacy</h1>',
            'status'               => 'legacy',
            'format'               => Document::FORMAT_HTML,
            'documentable_type'    => User::class,
            'documentable_id'      => $user->getKey(),
        ]);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$legacyDocument]);
    }

    public function test_referral_rewards_relation_manager_can_create_reward_for_user(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $owner = User::factory()->create();
        $referred = User::factory()->create();
        $referral = Referral::factory()->create([
            'referrer_id' => $owner->getKey(),
            'referred_id' => $referred->getKey(),
        ]);

        Livewire::test(ReferralRewardsRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.referral_id', $referral->getKey())
            ->set('mountedActions.0.data.title', 'Reward from tab')
            ->set('mountedActions.0.data.amount', 15.5)
            ->set('mountedActions.0.data.status', 'pending')
            ->set('mountedActions.0.data.type', 'referrer_bonus')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var ReferralReward|null $reward */
        $reward = ReferralReward::query()
            ->where('user_id', $owner->getKey())
            ->where('referral_id', $referral->getKey())
            ->where('type', 'referrer_bonus')
            ->latest('id')
            ->first();

        $this->assertNotNull($reward);
        $this->assertSame(15.5, (float) $reward->amount);
        $this->assertSame('pending', $reward->status);
        $this->assertSame('Reward from tab', $reward->getTranslation('title', 'lt'));
    }

    public function test_referral_rewards_relation_manager_defaults_status_when_not_provided(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $owner = User::factory()->create();
        $referred = User::factory()->create();
        $referral = Referral::factory()->create([
            'referrer_id' => $owner->getKey(),
            'referred_id' => $referred->getKey(),
        ]);

        Livewire::test(ReferralRewardsRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.referral_id', $referral->getKey())
            ->set('mountedActions.0.data.title', 'Auto status reward')
            ->set('mountedActions.0.data.amount', 9.99)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var ReferralReward|null $reward */
        $reward = ReferralReward::query()
            ->where('user_id', $owner->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($reward);
        $this->assertSame('pending', $reward->status);
        $this->assertSame('Auto status reward', $reward->getTranslation('title', 'lt'));
        $this->assertSame('referrer_bonus', $reward->type);
    }

    public function test_referral_rewards_relation_manager_can_create_reward_with_minimal_defaults(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $owner = User::factory()->create();

        Livewire::test(ReferralRewardsRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        /** @var ReferralReward|null $reward */
        $reward = ReferralReward::query()
            ->where('user_id', $owner->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($reward);
        $this->assertSame('pending', $reward->status);
        $this->assertSame('referrer_bonus', $reward->type);
        $this->assertSame(0.0, (float) $reward->amount);
        $this->assertSame('Referral reward', $reward->getTranslation('title', 'lt'));
    }

    public function test_view_user_page_relation_managers_can_create_records_for_reported_relations(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $owner = User::factory()->create();
        $groupCode = 'VIEW-GRP-' . strtoupper(substr(md5((string) microtime(true)), 0, 8));

        DocumentTemplate::query()->delete();

        Livewire::test(CustomerGroupsRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass'   => ViewUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.name', 'View page customer group')
            ->set('mountedActions.0.data.code', $groupCode)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        Livewire::test(OrdersRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass'   => ViewUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        Livewire::test(AddressesRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass'   => ViewUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.first_name', 'View')
            ->set('mountedActions.0.data.last_name', 'User')
            ->set('mountedActions.0.data.address_line_1', 'Test st. 1')
            ->set('mountedActions.0.data.city', 'Vilnius')
            ->set('mountedActions.0.data.postal_code', '01100')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass'   => ViewUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.title', 'View page document')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        Livewire::test(ReferralCodesRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass'   => ViewUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        Livewire::test(ReferralRewardsRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass'   => ViewUser::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertSame(1, $owner->customerGroups()->count());
        $this->assertSame(1, Order::query()->where('user_id', $owner->getKey())->count());
        $this->assertSame(1, Address::query()->where('user_id', $owner->getKey())->count());
        $this->assertSame(1, Document::withoutGlobalScopes()
            ->where('documentable_type', User::class)
            ->where('documentable_id', $owner->getKey())
            ->count());
        $this->assertSame(1, ReferralCode::withoutGlobalScopes()->where('user_id', $owner->getKey())->count());
        $this->assertSame(1, ReferralReward::query()->where('user_id', $owner->getKey())->count());
    }

    public function test_can_render_user_view_page_with_related_tabs(): void
    {
        $this->resolveAdminPanel();

        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();

        Livewire::test(ViewUser::class, [
            'record' => $user->getRouteKey(),
        ])
            ->assertSuccessful()
            ->assertSee(__('messages.profile'))
            ->assertSee(__('messages.orders'));
    }

    public function test_user_resource_registers_all_existing_relation_managers(): void
    {
        $relations = UserResource::getRelations();

        $this->assertEqualsCanonicalizing([
            CustomerGroupsRelationManager::class,
            PartnersRelationManager::class,
            OrdersRelationManager::class,
            AddressesRelationManager::class,
            CartItemsRelationManager::class,
            CouponUsagesRelationManager::class,
            DiscountRedemptionsRelationManager::class,
            NotificationsRelationManager::class,
            SubscriberRelationManager::class,
            DocumentsRelationManager::class,
            ReferralCodesRelationManager::class,
            ReferralsRelationManager::class,
            ReferralRewardsRelationManager::class,
        ], $relations);
    }
}
