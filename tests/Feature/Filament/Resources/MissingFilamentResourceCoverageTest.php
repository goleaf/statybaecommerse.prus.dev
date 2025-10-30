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
use App\Filament\Resources\EnumResource\Pages\ListEnums as EnumResourceListEnums;
use App\Filament\Resources\InventoryResource\Pages\ListInventories;
use App\Filament\Resources\LegalResource\Pages\ListLegals;
use App\Filament\Resources\LocationResource\Pages\ListLocations;
use App\Filament\Resources\NewsImageResource\Pages\ListNewsImages;
use App\Filament\Resources\NewsTagResource\Pages\ListNewsTags;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\PriceListItemResource\Pages\ListPriceListItems;
use App\Filament\Resources\PriceListResource\Pages\ListPriceLists;
use App\Filament\Resources\PriceResource\Pages\ListPrices;
use App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\ListRecommendationAnalytics;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigSimples;
use App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns;
use App\Filament\Resources\ReferralResource\Pages\ListReferrals;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories;
use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages\ListSystemSettingCategoryTranslations;
use App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings;
use App\Filament\Resources\UserManagementResource\Pages\ListUsers;
use App\Filament\Resources\UserPreferenceResource\Pages\ListUserPreferences;
use App\Filament\Resources\UserWishlistResource\Pages\ListUserWishlists;
use App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks;
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
use App\Models\NewsImage;
use App\Models\NewsTag;
use App\Models\Post;
use App\Models\Price;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
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
            'audit trails'                         => [ListAuditTrails::class, 'createAuditTrailRecord'],
            'brands'                               => [ListBrands::class, 'createBrandRecord'],
            'campaign conversions'                 => [ListCampaignConversions::class, 'createCampaignConversionRecord'],
            'campaign schedules'                   => [ListCampaignSchedules::class, 'createCampaignScheduleRecord'],
            'campaign views'                       => [ListCampaignViews::class, 'createCampaignViewRecord'],
            'campaigns'                            => [ListCampaigns::class, 'createCampaignRecord'],
            'cart items'                           => [ListCartItems::class, 'createCartItemRecord'],
            'cities'                               => [ListCities::class, 'createCityRecord'],
            'collections'                          => [ListCollections::class, 'createCollectionRecord'],
            'collection rules'                     => [ListCollectionRules::class, 'createCollectionRuleRecord'],
            'enum management'                      => [ListEnumManagement::class, 'createEnumValueRecord'],
            'enum values'                          => [EnumResourceListEnums::class, 'createEnumValueRecord'],
            'inventories'                          => [ListInventories::class, 'createInventoryRecord'],
            'legals'                               => [ListLegals::class, 'createLegalRecord'],
            'locations'                            => [ListLocations::class, 'createLocationRecord'],
            'news images'                          => [ListNewsImages::class, 'createNewsImageRecord'],
            'news tags'                            => [ListNewsTags::class, 'createNewsTagRecord'],
            'posts'                                => [ListPosts::class, 'createPostRecord'],
            'price list items'                     => [ListPriceListItems::class, 'createPriceListItemRecord'],
            'price lists'                          => [ListPriceLists::class, 'createPriceListRecord'],
            'prices'                               => [ListPrices::class, 'createPriceRecord'],
            'product variants'                     => [ListProductVariants::class, 'createProductVariantRecord'],
            'recommendation analytics'             => [ListRecommendationAnalytics::class, 'createRecommendationAnalyticsRecord'],
            'recommendation config simples'        => [ListRecommendationConfigSimples::class, 'createRecommendationConfigSimpleRecord'],
            'referral campaigns'                   => [ListReferralCampaigns::class, 'createReferralCampaignRecord'],
            'referrals'                            => [ListReferrals::class, 'createReferralRecord'],
            'roles'                                => [ListRoles::class, 'createRoleRecord'],
            'slider translations'                  => [ListSliderTranslations::class, 'createSliderTranslationRecord'],
            'system setting categories'            => [ListSystemSettingCategories::class, 'createSystemSettingCategoryRecord'],
            'system setting category translations' => [ListSystemSettingCategoryTranslations::class, 'createSystemSettingCategoryTranslationRecord'],
            'system settings'                      => [ListSystemSettings::class, 'createSystemSettingRecord'],
            'user management'                      => [ListUsers::class, 'createUserManagementRecord'],
            'user preferences'                     => [ListUserPreferences::class, 'createUserPreferenceRecord'],
            'user wishlists'                       => [ListUserWishlists::class, 'createUserWishlistRecord'],
            'variant stock'                        => [ListVariantStocks::class, 'createVariantInventoryRecord'],
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

    private function createCampaignConversionRecord(): CampaignConversion
    {
        // Seed a conversion with deterministic values so the listing highlights a completed purchase.
        return CampaignConversion::factory()->create([
            'conversion_type'  => 'purchase',
            'conversion_value' => 199.99,
            'status'           => 'completed',
            'source'           => 'google',
            'medium'           => 'cpc',
        ]);
    }

    private function createCampaignScheduleRecord(): CampaignSchedule
    {
        // Create a one-off schedule to confirm the timeline columns render predictable data.
        return CampaignSchedule::factory()->create([
            'schedule_type'   => 'once',
            'schedule_config' => [
                'time'      => '09:00',
                'timezone'  => 'UTC',
                'frequency' => 'one_time',
            ],
            'next_run_at' => now()->addDay(),
            'last_run_at' => now()->subDay(),
            'is_active'   => true,
        ]);
    }

    private function createCampaignViewRecord(): CampaignView
    {
        // Log a view event tied to a campaign so visitor analytics populate the Filament table.
        return CampaignView::factory()->create([
            'referer'   => 'https://example.com/landing',
            'user_agent' => 'Mozilla/5.0 FilamentCoverage',
            'viewed_at'  => now(),
        ]);
    }

    private function createCartItemRecord(): CartItem
    {
        // Persist a cart line with a known quantity and price to exercise subtotal calculations.
        return CartItem::factory()->create([
            'quantity'   => 2,
            'unit_price' => 45.50,
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

    private function createInventoryRecord(): Inventory
    {
        // Create an inventory entry with a stable SKU so stock tracking columns display meaningful data.
        return Inventory::factory()->create([
            'sku' => 'INV-COVERAGE-001',
            'qty' => 15,
        ]);
    }

    private function createLegalRecord(): Legal
    {
        // Seed a published legal document to ensure the compliance listings surface translated metadata.
        return Legal::factory()->create([
            'key'         => 'coverage-terms',
            'type'        => 'terms_of_use',
            'is_enabled'  => true,
            'is_required' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    private function createLocationRecord(): Location
    {
        // Link the location to a deterministic country so regional columns resolve human-readable names.
        $country = Country::factory()->create([
            'name' => 'Coverage Country',
            'code' => 'CV',
        ]);

        return Location::factory()->create([
            'name'         => 'Coverage Warehouse',
            'code'         => 'COV-WH',
            'country_code' => $country->cca2,
            'city'         => 'Coverage City',
            'type'         => 'warehouse',
            'is_enabled'   => true,
        ]);
    }

    private function createNewsImageRecord(): NewsImage
    {
        // Persist a news image with a predictable path so media previews render consistently in assertions.
        return NewsImage::factory()->create([
            'file_path' => 'news-images/coverage-hero.jpg',
            'alt_text'  => 'Coverage hero image',
            'caption'   => 'Coverage launch banner',
            'is_featured' => true,
        ]);
    }

    private function createNewsTagRecord(): NewsTag
    {
        // Generate a visible tag to confirm taxonomy filters list the seeded label.
        return NewsTag::factory()->create([
            'name' => 'Coverage Tag',
            'slug' => 'coverage-tag',
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

    private function createPriceListRecord(): PriceList
    {
        // Generate a dedicated price list so catalogue pricing tables have a parent container to display.
        return PriceList::factory()->create([
            'name' => 'Coverage Price List',
            'code' => 'COV-PL-' . Str::upper(Str::random(4)),
            'is_enabled' => true,
            'priority'   => 10,
        ]);
    }

    private function createPriceListItemRecord(): PriceListItem
    {
        // Attach a curated item to the seeded price list to showcase variant pricing data.
        $priceList = $this->createPriceListRecord();

        return PriceListItem::factory()
            ->for($priceList)
            ->create([
                'name' => [
                    'en' => 'Coverage Bundle',
                    'lt' => 'Coverage Bundle',
                ],
                'net_amount' => 79.99,
            ]);
    }

    private function createPriceRecord(): Price
    {
        // Persist a price tied to a concrete product and currency to validate pricing grids.
        $currency = Currency::factory()->create([
            'code'   => 'COV' . Str::upper(Str::random(2)),
            'symbol' => '¤',
        ]);

        $product = Product::factory()->create([
            'name' => 'Coverage Product',
            'slug' => 'coverage-product',
        ]);

        return Price::factory()
            ->for($currency, 'currency')
            ->state([
                'priceable_type' => $product->getMorphClass(),
                'priceable_id'   => $product->getKey(),
                'amount'         => 49.99,
                'compare_amount' => 59.99,
                'is_enabled'     => true,
            ])
            ->create();
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

    private function createRecommendationConfigSimpleRecord(): RecommendationConfigSimple
    {
        // Build a lightweight recommendation configuration to ensure the simplified config grid renders entries.
        return RecommendationConfigSimple::factory()->create([
            'name'           => 'Coverage Recommendations',
            'code'           => 'coverage-config',
            'algorithm_type' => 'collaborative',
            'is_active'      => true,
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

    private function createReferralRecord(): Referral
    {
        // Seed a completed referral so lifecycle badges in the listing showcase a positive state.
        return Referral::factory()->create([
            'status'        => 'completed',
            'referral_code' => 'COVERAGE',
        ]);
    }

    private function createRoleRecord(): Role
    {
        // Provision a custom role so the authorization tables expose additional entries beyond defaults.
        return Role::factory()->create([
            'name'       => 'coverage_manager',
            'guard_name' => 'web',
        ]);
    }

    private function createSliderTranslationRecord(): SliderTranslation
    {
        // Persist a slider translation entry to validate the localized slider management grid.
        return SliderTranslation::factory()->english()->create([
            'title' => 'Coverage Slide',
        ]);
    }

    private function createSystemSettingCategoryRecord(): SystemSettingCategory
    {
        // Create a root settings category so administrative filters list the seeded grouping.
        return SystemSettingCategory::factory()->create([
            'name' => 'Coverage Settings',
            'slug' => 'coverage-settings',
        ]);
    }

    private function createSystemSettingCategoryTranslationRecord(): SystemSettingCategoryTranslation
    {
        // Attach an English translation to a settings category to confirm localized labels display correctly.
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Coverage Settings',
            'slug' => 'coverage-settings',
        ]);

        return SystemSettingCategoryTranslation::factory()
            ->for($category)
            ->english()
            ->create([
                'name'        => 'Coverage Settings',
                'description' => 'Coverage specific configuration',
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

    private function createUserWishlistRecord(): UserWishlist
    {
        // Persist a private wishlist so customer engagement tables feature personalized collections.
        return UserWishlist::factory()->create([
            'name'       => 'Coverage Wishlist',
            'is_public'  => false,
            'is_default' => true,
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
}
