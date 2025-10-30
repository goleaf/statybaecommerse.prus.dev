<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\AuditTrailResource\Pages\ListAuditTrails;
use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Filament\Resources\CampaignConversionResource\Pages\ListCampaignConversions;
use App\Filament\Resources\CampaignResource\Pages\ListCampaigns;
use App\Filament\Resources\CampaignScheduleResource\Pages\ListCampaignSchedules;
use App\Filament\Resources\CampaignViewResource\Pages\ListCampaignViews;
use App\Filament\Resources\CartItemResource\Pages\ListCartItems;
use App\Filament\Resources\CityResource\Pages\ListCities;
use App\Filament\Resources\CollectionResource\Pages\ListCollections;
use App\Filament\Resources\CollectionRuleResource\Pages\ListCollectionRules;
use App\Filament\Resources\EnumManagementResource\Pages\ListEnumManagement;
use App\Filament\Resources\EnumManagementResource\Pages\ListEnums as EnumManagementListEnums;
use App\Filament\Resources\EnumResource\Pages\ListEnums as EnumResourceListEnums;
use App\Filament\Resources\InventoryResource\Pages\ListInventories;
use App\Filament\Resources\LegalResource\Pages\ListLegals;
use App\Filament\Resources\LocationResource\Pages\ListLocations;
use App\Filament\Resources\NewsImageResource\Pages\ListNewsImages as NewsImageResourceListNewsImages;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages as LegacyNewsImagesListNewsImages;
use App\Filament\Resources\NewsTagResource\Pages\ListNewsTags as NewsTagResourceListNewsTags;
use App\Filament\Resources\NewsTags\Pages\ListNewsTags as LegacyNewsTagsListNewsTags;
use App\Filament\Resources\PriceListItemResource\Pages\ListPriceListItems;
use App\Filament\Resources\PriceListResource\Pages\ListPriceLists;
use App\Filament\Resources\PriceResource\Pages\ListPrices;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\ListRecommendationAnalytics;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigResourceSimples;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigSimples;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigsSimple;
use App\Filament\Resources\ReferralResource\Pages\ListReferrals;
use App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Filament\Resources\SystemSettingCategories\Pages\ListSystemSettingCategories as LegacySystemSettingCategoriesList;
use App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories as SystemSettingCategoryResourceList;
use App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings;
use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages\ListSystemSettingCategoryTranslations;
use App\Filament\Resources\UserWishlistResource\Pages\ListUserWishlists;
use App\Filament\Resources\UserManagementResource\Pages\ListUsers;
use App\Filament\Resources\UserPreferenceResource\Pages\ListUserPreferences;
use App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks;
use App\Enums\ScheduleType;
use App\Models\AuditTrail;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\CampaignSchedule;
use App\Models\CampaignView;
use App\Models\CartItem;
use App\Models\City;
use App\Models\Collection;
use App\Models\CollectionRule;
use App\Models\Country;
use App\Models\Currency;
use App\Models\EnumValue;
use App\Models\Inventory;
use App\Models\Legal;
use App\Models\Location;
use App\Models\News;
use App\Models\NewsImage;
use App\Models\NewsTag;
use App\Models\Post;
use App\Models\Price;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\ProductVariant;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationConfigSimple;
use App\Models\Referral;
use App\Models\ReferralCampaign;
use App\Models\Role;
use App\Models\SliderTranslation;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\UserWishlist;
use App\Models\VariantInventory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Consolidated smoke tests covering Filament resources that previously missed feature coverage.
 */
final class MissingFilamentResourceCoverageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel context before any Livewire components boot.
        $this->resolveAdminPanel();

        // Normalise locales so translated factories return deterministic English strings.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Disable heavy campaign relation seeding to keep these smoke tests fast and focused.
        config(['factory.seed_campaign_relations' => false]);

        // Authenticate as an admin user so resource authorization checks pass automatically.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    /**
     * @return array<string, array{class-string, string}>
     */
    public static function resourceProvider(): array
    {
        // Map each resource list page to the helper responsible for creating a visible record.
        return [
            'audit trails'              => [ListAuditTrails::class, 'createAuditTrailRecord'],
            'brands'                    => [ListBrands::class, 'createBrandRecord'],
            'campaign conversions'      => [ListCampaignConversions::class, 'createCampaignConversionRecord'],
            'campaign schedules'        => [ListCampaignSchedules::class, 'createCampaignScheduleRecord'],
            'campaign views'            => [ListCampaignViews::class, 'createCampaignViewRecord'],
            'campaigns'                 => [ListCampaigns::class, 'createCampaignRecord'],
            'cart items'                => [ListCartItems::class, 'createCartItemRecord'],
            'cities'                    => [ListCities::class, 'createCityRecord'],
            'collections'               => [ListCollections::class, 'createCollectionRecord'],
            'collection rules'          => [ListCollectionRules::class, 'createCollectionRuleRecord'],
            'enum management'           => [ListEnumManagement::class, 'createEnumValueRecord'],
            'enum management nested'    => [EnumManagementListEnums::class, 'createEnumValueRecord'],
            'enum values'               => [EnumResourceListEnums::class, 'createEnumValueRecord'],
            'inventories'               => [ListInventories::class, 'createInventoryRecord'],
            'legal documents'           => [ListLegals::class, 'createLegalRecord'],
            'locations'                 => [ListLocations::class, 'createLocationRecord'],
            'news images'               => [NewsImageResourceListNewsImages::class, 'createNewsImageRecord'],
            'news images legacy'        => [LegacyNewsImagesListNewsImages::class, 'createNewsImageRecord'],
            'news tags'                 => [NewsTagResourceListNewsTags::class, 'createNewsTagRecord'],
            'news tags legacy'          => [LegacyNewsTagsListNewsTags::class, 'createNewsTagRecord'],
            'posts'                     => [ListPosts::class, 'createPostRecord'],
            'price list items'          => [ListPriceListItems::class, 'createPriceListItemRecord'],
            'price lists'               => [ListPriceLists::class, 'createPriceListRecord'],
            'prices'                    => [ListPrices::class, 'createPriceRecord'],
            'product variants'          => [ListProductVariants::class, 'createProductVariantRecord'],
            'recommendation analytics'  => [ListRecommendationAnalytics::class, 'createRecommendationAnalyticsRecord'],
            'recommendation configs'    => [ListRecommendationConfigSimples::class, 'createRecommendationConfigSimpleRecord'],
            'recommendation configs legacy' => [ListRecommendationConfigResourceSimples::class, 'createRecommendationConfigSimpleRecord'],
            'recommendation configs plural' => [ListRecommendationConfigsSimple::class, 'createRecommendationConfigSimpleRecord'],
            'referrals'                 => [ListReferrals::class, 'createReferralRecord'],
            'referral campaigns'        => [ListReferralCampaigns::class, 'createReferralCampaignRecord'],
            'roles'                     => [ListRoles::class, 'createRoleRecord'],
            'slider translations'       => [ListSliderTranslations::class, 'createSliderTranslationRecord'],
            'system setting categories' => [LegacySystemSettingCategoriesList::class, 'createSystemSettingCategoryRecord'],
            'system setting categories resource' => [SystemSettingCategoryResourceList::class, 'createSystemSettingCategoryRecord'],
            'system setting category translations' => [ListSystemSettingCategoryTranslations::class, 'createSystemSettingCategoryTranslationRecord'],
            'system settings'           => [ListSystemSettings::class, 'createSystemSettingRecord'],
            'user management'           => [ListUsers::class, 'createUserManagementRecord'],
            'user preferences'          => [ListUserPreferences::class, 'createUserPreferenceRecord'],
            'user wishlists'            => [ListUserWishlists::class, 'createUserWishlistRecord'],
            'variant stock'             => [ListVariantStocks::class, 'createVariantInventoryRecord'],
        ];
    }

    /**
     * @dataProvider resourceProvider
     */
    public function test_list_pages_render_seeded_records(string $pageClass, string $factoryMethod): void
    {
        // Seed a record using the dedicated helper so each resource table has something to display.
        $record = $this->{$factoryMethod}();

        // Hydrate the table data explicitly before asserting the seeded record is visible.
        Livewire::test($pageClass)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$record]);
    }

    private function createAuditTrailRecord(): AuditTrail
    {
        // Create actor and auditable users so the audit entry can resolve morph targets.
        $actor = User::factory()->create(['name' => 'Audit Actor']);
        $subject = User::factory()->create(['name' => 'Subject User']);

        // Persist a minimal audit trail entry with a deterministic diff payload for table assertions.
        return AuditTrail::query()->create([
            'auditable_type' => $subject->getMorphClass(),
            'auditable_id'   => $subject->getKey(),
            'event'          => 'user.updated',
            'actor_type'     => $actor->getMorphClass(),
            'actor_id'       => $actor->getKey(),
            'reason'         => 'Unit coverage',
            'request_id'     => (string) Str::uuid(),
            'diff'           => [
                'name' => [
                    'previous' => 'Old Name',
                    'current'  => 'New Name',
                ],
            ],
        ]);
    }

    private function createBrandRecord(): Brand
    {
        // Use the factory to create a visible brand that the table listing can display immediately.
        return Brand::factory()->create([
            'name' => 'Coverage Brand',
            'slug' => 'coverage-brand',
        ]);
    }

    private function createCampaignRecord(): Campaign
    {
        // Generate a simple active campaign so marketing listings show a concrete entry.
        return Campaign::factory()->create([
            'name' => 'Coverage Campaign',
            'slug' => 'coverage-campaign',
        ]);
    }

    private function createCityRecord(): City
    {
        // Attach the city to a country so dependent table columns (country name/code) render correctly.
        $country = Country::factory()->create([
            'name' => 'Coverage Country',
            'code' => 'CC',
        ]);

        return City::factory()->for($country)->create([
            'name' => 'Coverage City',
            'slug' => 'coverage-city',
        ]);
    }

    private function createCollectionRecord(): Collection
    {
        // Create a manual collection that remains visible to administrators by default.
        return Collection::factory()->create([
            'name' => 'Coverage Collection',
            'slug' => 'coverage-collection',
        ]);
    }

    private function createCollectionRuleRecord(): CollectionRule
    {
        // Ensure the rule references a known collection so relational columns hydrate inside Filament.
        $collection = Collection::factory()->create([
            'name' => 'Rule Host Collection',
            'slug' => 'rule-host-collection',
        ]);

        return CollectionRule::factory()->for($collection)->create([
            'field'    => 'status',
            'operator' => 'equals',
            'value'    => 'active',
        ]);
    }

    private function createEnumValueRecord(): EnumValue
    {
        // Persist an active enum value so the navigation badge logic treats it as part of the dataset.
        return EnumValue::factory()->active()->create([
            'type'  => 'navigation_group',
            'key'   => 'coverage',
            'value' => 'Coverage',
        ]);
    }

    private function createPostRecord(): Post
    {
        // Seed a published post entry to exercise the marketing/content management listings.
        return Post::factory()->create([
            'title' => 'Coverage Post',
            'slug'  => 'coverage-post',
        ]);
    }

    private function createProductVariantRecord(): ProductVariant
    {
        // Use the factory to provision a variant with an associated product for catalog checks.
        return ProductVariant::factory()->create([
            'name' => 'Coverage Variant',
            'sku'  => 'COVERAGE001',
        ]);
    }

    private function createRecommendationAnalyticsRecord(): RecommendationAnalytics
    {
        // Generate analytics metrics so reporting tables showcase actionable rows.
        return RecommendationAnalytics::factory()->create([
            'action' => 'view',
        ]);
    }

    private function createReferralCampaignRecord(): ReferralCampaign
    {
        // Create a bilingual referral campaign so localized columns render deterministic strings.
        return ReferralCampaign::factory()->create([
            'name' => [
                'en' => 'Coverage Referral Campaign',
                'lt' => 'Draudimo referral kampanija',
            ],
            'is_active' => true,
        ]);
    }

    private function createSliderTranslationRecord(): SliderTranslation
    {
        // Persist a slider translation entry to validate the localized slider management grid.
        return SliderTranslation::factory()->english()->create([
            'title' => 'Coverage Slide',
        ]);
    }

    private function createSystemSettingRecord(): SystemSetting
    {
        // Store a simple system setting so configuration tables reflect active entries.
        return SystemSetting::factory()->create([
            'key'   => 'coverage_setting',
            'name'  => 'Coverage Setting',
            'group' => 'general',
        ]);
    }

    private function createUserManagementRecord(): User
    {
        // Provision a regular user to surface inside the consolidated user management listing.
        return User::factory()->create([
            'name'  => 'Coverage User',
            'email' => 'coverage.user@example.com',
        ]);
    }

    private function createUserPreferenceRecord(): UserPreference
    {
        // Attach a high score preference to a dedicated user so the preference grid has context.
        $user = User::factory()->create([
            'email' => 'coverage.preference@example.com',
        ]);

        return UserPreference::factory()->forUser($user)->highScore()->create([
            'preference_type' => 'category',
            'preference_key'  => 'coverage-category',
        ]);
    }

    private function createVariantInventoryRecord(): VariantInventory
    {
        // Generate a stocked inventory entry to verify the inventory dashboards hydrate successfully.
        return VariantInventory::factory()->create([
            'stock'     => 25,
            'reserved'  => 5,
            'threshold' => 3,
        ]);
    }

    private function createCampaignConversionRecord(): CampaignConversion
    {
        // Compose supporting campaign metadata so the analytics table can resolve related campaign names without hitting nulls.
        $campaign = Campaign::factory()->create([
            'name' => 'Coverage Conversion Campaign',
            'slug' => 'coverage-conversion-campaign',
        ]);

        // Persist a completed conversion with deterministic fields so table filters remain predictable during assertions.
        return CampaignConversion::query()->create([
            'campaign_id'      => $campaign->getKey(),
            'customer_id'      => $this->admin->getKey(),
            'conversion_type'  => 'purchase',
            'conversion_value' => 123.45,
            'status'           => 'completed',
            'session_id'       => 'coverage-session',
            'source'           => 'email',
            'medium'           => 'newsletter',
            'device_type'      => 'desktop',
            'converted_at'     => now()->subHour(),
        ]);
    }

    private function createCampaignScheduleRecord(): CampaignSchedule
    {
        // Seed the underlying campaign so the schedule row can display the owning campaign name within the listing.
        $campaign = Campaign::factory()->create([
            'name' => 'Schedule Host Campaign',
            'slug' => 'schedule-host-campaign',
        ]);

        // Create a daily schedule with an explicit JSON payload to mirror realistic administrator input.
        return CampaignSchedule::query()->create([
            'campaign_id'     => $campaign->getKey(),
            'schedule_type'   => ScheduleType::DAILY->value,
            'schedule_config' => [
                'time'      => '09:00',
                'timezone'  => 'UTC',
                'frequency' => 'every_day',
            ],
            'next_run_at'     => now()->addDay(),
            'last_run_at'     => now()->subDay(),
            'is_active'       => true,
        ]);
    }

    private function createCampaignViewRecord(): CampaignView
    {
        // Provision a campaign so the view entry references a valid marketing initiative in the UI.
        $campaign = Campaign::factory()->create([
            'name' => 'Coverage View Campaign',
            'slug' => 'coverage-view-campaign',
        ]);

        // Store a tracked view tied to the authenticated admin to verify scoped listings render expected traffic rows.
        return CampaignView::query()->create([
            'campaign_id' => $campaign->getKey(),
            'session_id'  => 'coverage-view-session',
            'ip_address'  => '192.0.2.10',
            'user_agent'  => 'Coverage Browser',
            'referer'     => 'https://example.com',
            'customer_id' => $this->admin->getKey(),
            'viewed_at'   => now()->subMinutes(15),
        ]);
    }

    private function createCartItemRecord(): CartItem
    {
        // Create a simple product so the cart line item can resolve product level columns like SKU and name.
        $product = Product::factory()->create([
            'name' => 'Coverage Cart Product',
            'slug' => 'coverage-cart-product',
        ]);

        // Ensure the cart item belongs to the authenticated admin to satisfy the user-owned global scope applied to the model.
        return CartItem::factory()->create([
            'user_id'     => $this->admin->getKey(),
            'product_id'  => $product->getKey(),
            'session_id'  => 'coverage-cart-session',
            'quantity'    => 2,
            'unit_price'  => 59.99,
            'price'       => 59.99,
            'total_price' => 119.98,
        ]);
    }

    private function createInventoryRecord(): Inventory
    {
        // Provision a dedicated warehouse so the inventory listing can present a concrete fulfilment location.
        $location = Location::factory()->create([
            'code' => 'COV-WH',
            'name' => 'Coverage Warehouse',
            'type' => 'warehouse',
        ]);

        // Persist a stocked inventory row tied to the warehouse to keep the table hydrated with actionable quantity data.
        return Inventory::factory()->create([
            'warehouse_id' => $location->getKey(),
            'sku'          => 'COV-SKU-001',
            'qty'          => 75,
        ]);
    }

    private function createLegalRecord(): Legal
    {
        // Store an enabled legal document so compliance tables render a visible policy entry.
        return Legal::factory()->create([
            'key'         => 'coverage-policy',
            'type'        => 'privacy_policy',
            'is_enabled'  => true,
            'is_required' => true,
            'sort_order'  => 1,
        ]);
    }

    private function createLocationRecord(): Location
    {
        // Seed a warehouse style location to exercise logistics specific table columns.
        return Location::factory()->create([
            'code'         => 'COV-LOC',
            'name'         => 'Coverage Logistics Hub',
            'slug'         => 'coverage-logistics-hub',
            'type'         => 'warehouse',
            'city'         => 'Coverage City',
            'country_code' => 'LT',
            'is_enabled'   => true,
        ]);
    }

    private function createNewsImageRecord(): NewsImage
    {
        // Pair the image with a published news article so foreign key constraints remain satisfied.
        $news = News::factory()->create([
            'author_name' => 'Coverage Reporter',
        ]);

        // Capture a featured image with a deterministic caption for reliable assertion behaviour.
        return NewsImage::factory()->create([
            'news_id'     => $news->getKey(),
            'file_path'   => 'news-images/coverage.jpg',
            'alt_text'    => 'Coverage illustration',
            'caption'     => 'Coverage campaign artwork',
            'is_featured' => true,
            'sort_order'  => 1,
        ]);
    }

    private function createNewsTagRecord(): NewsTag
    {
        // Persist a visible tag so editorial listings showcase a concrete taxonomy entry.
        return NewsTag::factory()->create([
            'name'       => 'Coverage Tag',
            'slug'       => 'coverage-tag',
            'is_visible' => true,
            'is_active'  => true,
            'sort_order' => 5,
        ]);
    }

    private function createPriceListRecord(): PriceList
    {
        // Guarantee a base currency so downstream price list associations resolve predictable exchange metadata.
        $currency = Currency::factory()->create([
            'code'          => 'EUR',
            'name'          => 'Euro',
            'symbol'        => '€',
            'exchange_rate' => 1.0,
            'is_default'    => true,
        ]);

        // Create an enabled price list that anchors subsequent item fixtures in other helpers.
        return PriceList::factory()->create([
            'name'        => 'Coverage Price List',
            'code'        => 'coverage-price-list',
            'currency_id' => $currency->getKey(),
            'is_enabled'  => true,
        ]);
    }

    private function createPriceListItemRecord(): PriceListItem
    {
        // Reuse a deterministic price list so related tables can surface the parent pricing context.
        $priceList = $this->createPriceListRecord();

        // Couple the list item with a tangible product variant so relational columns remain populated.
        $product = Product::factory()->create([
            'name' => 'Coverage Price Item',
            'slug' => 'coverage-price-item',
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'name' => 'Coverage Item Variant',
            'sku'  => 'COV-VAR-001',
        ]);

        // Persist an active line item with translations to mimic real administrator input across locales.
        return PriceListItem::factory()->create([
            'price_list_id'  => $priceList->getKey(),
            'product_id'     => $product->getKey(),
            'variant_id'     => $variant->getKey(),
            'name'           => ['en' => 'Coverage Item', 'lt' => 'Coverage Item'],
            'description'    => ['en' => 'Coverage discount entry', 'lt' => 'Coverage discount entry'],
            'notes'          => ['en' => 'Coverage note', 'lt' => 'Coverage note'],
            'net_amount'     => 49.99,
            'compare_amount' => 59.99,
            'is_active'      => true,
            'priority'       => 10,
            'valid_from'     => now()->subDay(),
            'valid_until'    => now()->addMonth(),
        ]);
    }

    private function createPriceRecord(): Price
    {
        // Establish a base currency and product so the polymorphic relationship resolves within the listing grid.
        $currency = Currency::factory()->create([
            'code'          => 'USD',
            'name'          => 'US Dollar',
            'symbol'        => '$',
            'exchange_rate' => 1.1,
        ]);

        $product = Product::factory()->create([
            'name' => 'Coverage Price Product',
            'slug' => 'coverage-price-product',
        ]);

        // Manually persist the price record to control the morph targets and enabled state used inside the Filament table.
        return Price::query()->create([
            'priceable_type' => Product::class,
            'priceable_id'   => $product->getKey(),
            'currency_id'    => $currency->getKey(),
            'amount'         => 199.99,
            'compare_amount' => 249.99,
            'cost_amount'    => 129.99,
            'type'           => 'base',
            'is_enabled'     => true,
            'metadata'       => ['label' => 'Coverage Base Price'],
        ]);
    }

    private function createRecommendationConfigSimpleRecord(): RecommendationConfigSimple
    {
        // Create an active simplified configuration so recommendation admin tables surface non-empty datasets.
        return RecommendationConfigSimple::factory()->create([
            'name'        => 'Coverage Recommendation Config',
            'code'        => 'coverage-config',
            'is_active'   => true,
            'is_default'  => false,
            'max_results' => 5,
        ]);
    }

    private function createReferralRecord(): Referral
    {
        // Prepare distinct referrer and referred users to satisfy the relational columns surfaced by the listing.
        $referrer = User::factory()->create([
            'name'  => 'Coverage Referrer',
            'email' => 'coverage.referrer@example.com',
        ]);

        $referred = User::factory()->create([
            'name'  => 'Coverage Referred',
            'email' => 'coverage.referred@example.com',
        ]);

        // Persist an active referral with translated marketing copy to align with the resource expectations.
        return Referral::query()->create([
            'referrer_id'         => $referrer->getKey(),
            'referred_id'         => $referred->getKey(),
            'referral_code'       => 'COVERAGECODE',
            'status'              => 'active',
            'title'               => ['en' => 'Coverage Referral'],
            'description'         => ['en' => 'Coverage referral description'],
            'terms_conditions'    => ['en' => 'Coverage referral terms'],
            'benefits_description'=> ['en' => 'Coverage benefits'],
        ]);
    }

    private function createRoleRecord(): Role
    {
        // Store a custom role so the permission management grid renders at least one administrator defined entry.
        return Role::factory()->create([
            'name'       => 'coverage_role',
            'guard_name' => 'web',
        ]);
    }

    private function createSystemSettingCategoryRecord(): SystemSettingCategory
    {
        // Persist an active settings category so configuration tables display a tangible grouping.
        return SystemSettingCategory::factory()->create([
            'name'      => 'Coverage Settings',
            'slug'      => 'coverage-settings',
            'is_active' => true,
            'sort_order'=> 10,
        ]);
    }

    private function createSystemSettingCategoryTranslationRecord(): SystemSettingCategoryTranslation
    {
        // Reuse a dedicated category so the translation entry can reference an existing parent row.
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Coverage Translation Category',
            'slug' => 'coverage-translation-category',
        ]);

        // Insert an English translation to match the default locale asserted earlier in the test.
        return SystemSettingCategoryTranslation::factory()->create([
            'system_setting_category_id' => $category->getKey(),
            'locale'                     => 'en',
            'name'                       => 'Coverage Category',
            'description'                => 'Coverage category description',
        ]);
    }

    private function createUserWishlistRecord(): UserWishlist
    {
        // Attach the wishlist to the authenticated admin to satisfy the user-owned global scope on the model.
        return UserWishlist::factory()->create([
            'user_id'    => $this->admin->getKey(),
            'name'       => 'Coverage Wishlist',
            'description'=> 'Coverage wishlist description',
            'is_public'  => true,
            'is_default' => false,
        ]);
    }
}
