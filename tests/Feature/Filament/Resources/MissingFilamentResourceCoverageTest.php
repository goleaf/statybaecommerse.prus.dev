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
use App\Filament\Resources\EnumManagementResource\Pages\ListEnums as ListEnumManagementLegacy;
use App\Filament\Resources\EnumResource\Pages\ListEnums as ListEnumsResource;
use App\Filament\Resources\InventoryResource\Pages\ListInventories;
use App\Filament\Resources\LegalResource\Pages\ListLegal;
use App\Filament\Resources\LegalResource\Pages\ListLegals;
use App\Filament\Resources\LocationResource\Pages\ListLocations;
use App\Filament\Resources\NewsImageResource\Pages\ListNewsImages as ListNewsImagesResource;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages as ListNestedNewsImages;
use App\Filament\Resources\NewsTagResource\Pages\ListNewsTags as ListNewsTagsResource;
use App\Filament\Resources\NewsTags\Pages\ListNewsTags as ListNestedNewsTags;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\PriceListItemResource\Pages\ListPriceListItems;
use App\Filament\Resources\PriceListResource\Pages\ListPriceLists;
use App\Filament\Resources\PriceResource\Pages\ListPrices;
use App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\ListRecommendationAnalytics;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigResourceSimples;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigSimples;
use App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns;
use App\Filament\Resources\ReferralResource\Pages\ListReferrals;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Resources\SettingResource\Pages\ListSettings as ListSettingsResource;
use App\Filament\Resources\Settings\Pages\ListSettings as ListNestedSettings;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Filament\Resources\SystemSettingCategories\Pages\ListSystemSettingCategories as ListNestedSystemSettingCategories;
use App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories as ListSystemSettingCategoryPage;
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
use App\Models\Setting;
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
            'audit trails'                              => [ListAuditTrails::class, 'createAuditTrailRecord'],
            'brands'                                    => [ListBrands::class, 'createBrandRecord'],
            'campaign conversions'                      => [ListCampaignConversions::class, 'createCampaignConversionRecord'],
            'campaign schedules'                        => [ListCampaignSchedules::class, 'createCampaignScheduleRecord'],
            'campaign views'                            => [ListCampaignViews::class, 'createCampaignViewRecord'],
            'campaigns'                                 => [ListCampaigns::class, 'createCampaignRecord'],
            'cart items'                                => [ListCartItems::class, 'createCartItemRecord'],
            'cities'                                    => [ListCities::class, 'createCityRecord'],
            'collections'                               => [ListCollections::class, 'createCollectionRecord'],
            'collection rules'                          => [ListCollectionRules::class, 'createCollectionRuleRecord'],
            'enum management'                           => [ListEnumManagement::class, 'createEnumValueRecord'],
            'enum management base list'                 => [ListEnumManagementLegacy::class, 'createEnumValueRecord'],
            'enum resource list'                        => [ListEnumsResource::class, 'createEnumValueRecord'],
            'inventories'                               => [ListInventories::class, 'createInventoryRecord'],
            'legal base list'                           => [ListLegal::class, 'createLegalRecord'],
            'legal tabbed list'                         => [ListLegals::class, 'createLegalRecord'],
            'locations'                                 => [ListLocations::class, 'createLocationRecord'],
            'news images resource'                      => [ListNewsImagesResource::class, 'createNewsImageRecord'],
            'news images nested resource'               => [ListNestedNewsImages::class, 'createNewsImageRecord'],
            'news tags resource'                        => [ListNewsTagsResource::class, 'createNewsTagRecord'],
            'news tags nested resource'                 => [ListNestedNewsTags::class, 'createNewsTagRecord'],
            'posts'                                     => [ListPosts::class, 'createPostRecord'],
            'price list items'                          => [ListPriceListItems::class, 'createPriceListItemRecord'],
            'price lists'                               => [ListPriceLists::class, 'createPriceListRecord'],
            'prices'                                    => [ListPrices::class, 'createPriceRecord'],
            'product variants'                          => [ListProductVariants::class, 'createProductVariantRecord'],
            'recommendation analytics'                  => [ListRecommendationAnalytics::class, 'createRecommendationAnalyticsRecord'],
            'recommendation config simple base'         => [ListRecommendationConfigResourceSimples::class, 'createRecommendationConfigSimpleRecord'],
            'recommendation config simple legacy list'  => [ListRecommendationConfigSimples::class, 'createRecommendationConfigSimpleRecord'],
            'referral campaigns'                        => [ListReferralCampaigns::class, 'createReferralCampaignRecord'],
            'referrals'                                 => [ListReferrals::class, 'createReferralRecord'],
            'roles'                                     => [ListRoles::class, 'createRoleRecord'],
            'settings resource list'                    => [ListSettingsResource::class, 'createSettingRecord'],
            'settings shared module list'               => [ListNestedSettings::class, 'createSettingRecord'],
            'slider translations'                       => [ListSliderTranslations::class, 'createSliderTranslationRecord'],
            'system setting categories nested resource' => [ListNestedSystemSettingCategories::class, 'createSystemSettingCategoryRecord'],
            'system setting categories resource'        => [ListSystemSettingCategoryPage::class, 'createSystemSettingCategoryRecord'],
            'system setting category translations'      => [ListSystemSettingCategoryTranslations::class, 'createSystemSettingCategoryTranslationRecord'],
            'system settings'                           => [ListSystemSettings::class, 'createSystemSettingRecord'],
            'user management'                           => [ListUsers::class, 'createUserManagementRecord'],
            'user preferences'                          => [ListUserPreferences::class, 'createUserPreferenceRecord'],
            'user wishlists'                            => [ListUserWishlists::class, 'createUserWishlistRecord'],
            'variant stock'                             => [ListVariantStocks::class, 'createVariantInventoryRecord'],
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

    private function createCampaignConversionRecord(): CampaignConversion
    {
        // Seed a conversion event linking to a marketing campaign so analytics tables surface a row.
        return CampaignConversion::factory()->create([
            'conversion_type'  => 'coverage_purchase',
            'conversion_value' => 123.45,
            'status'           => 'completed',
            'session_id'       => 'coverage-session',
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

    private function createCampaignScheduleRecord(): CampaignSchedule
    {
        // Provide a daily schedule to verify Filament renders timing metadata without extra fixtures.
        return CampaignSchedule::factory()->create([
            'schedule_type'   => 'daily',
            'schedule_config' => [
                'time'      => '08:00',
                'timezone'  => 'UTC',
                'frequency' => 'every_day',
            ],
            'is_active'   => true,
            'next_run_at' => now()->addDay(),
            'last_run_at' => now()->subDay(),
        ]);
    }

    private function createCampaignViewRecord(): CampaignView
    {
        // Capture a deterministic view event so attribution dashboards have visitor activity to render.
        return CampaignView::factory()->create([
            'session_id' => 'coverage-view-session',
            'referer'    => 'https://example.com/landing',
            'viewed_at'  => now()->subMinutes(5),
        ]);
    }

    private function createCartItemRecord(): CartItem
    {
        // Craft a cart item tied to a session to prove the cart management grid hydrates correctly.
        return CartItem::factory()->create([
            'session_id' => 'coverage-cart',
            'quantity'   => 2,
            'unit_price' => 49.99,
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

    private function createInventoryRecord(): Inventory
    {
        // Provision a tracked inventory row so warehouse summaries display stock levels.
        return Inventory::factory()->create([
            'sku' => 'INV-COVERAGE',
            'qty' => 42,
            'meta' => [
                'reserved'   => 4,
                'incoming'   => 8,
                'threshold'  => 5,
                'is_tracked' => true,
            ],
        ]);
    }

    private function createLegalRecord(): Legal
    {
        // Create a published legal document so compliance listings surface a visible policy entry.
        return Legal::factory()->create([
            'key'         => 'coverage-terms',
            'type'        => 'terms_of_use',
            'is_enabled'  => true,
            'is_required' => true,
            'sort_order'  => 1,
            'published_at' => now()->subDay(),
        ]);
    }

    private function createLocationRecord(): Location
    {
        // Register a warehouse location so logistics screens have an enabled facility to display.
        return Location::factory()->create([
            'name'           => 'Coverage Warehouse',
            'slug'           => 'coverage-warehouse',
            'code'           => 'COV',
            'address_line_1' => '1 Coverage Way',
            'city'           => 'Vilnius',
            'country_code'   => null,
            'is_enabled'     => true,
        ]);
    }

    private function createNewsImageRecord(): NewsImage
    {
        // Persist a featured news image so media management tables render preview metadata.
        return NewsImage::factory()->featured()->create([
            'file_path' => 'news-images/coverage-hero.jpg',
            'caption'   => 'Coverage Hero Image',
            'alt_text'  => 'Coverage alt text',
        ]);
    }

    private function createNewsTagRecord(): NewsTag
    {
        // Create an active, visible tag to populate editorial taxonomy listings.
        return NewsTag::factory()->active()->create([
            'name' => 'Coverage Tag',
            'slug' => 'coverage-tag',
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

    private function createPriceListItemRecord(): PriceListItem
    {
        // Attach a featured price list item so merchandising grids display tiered pricing rows.
        return PriceListItem::factory()->create([
            'net_amount' => 79.99,
            'name'       => [
                'en' => 'Coverage Price Item',
                'lt' => 'Coverage Price Item',
            ],
            'is_active'  => true,
        ]);
    }

    private function createPriceListRecord(): PriceList
    {
        // Produce an enabled price list with active dates so rule-based pricing dashboards show data.
        $currency = Currency::factory()->create([
            'code'     => 'COV',
            'iso_code' => 'COV-001',
            'name'     => 'Coverage Currency',
            'symbol'   => '¤',
            'is_enabled' => true,
            'is_active'  => true,
        ]);

        return PriceList::factory()->create([
            'name'        => [
                'en' => 'Coverage Price List',
                'lt' => 'Coverage Price List',
            ],
            'code'        => 'coverage-prices',
            'currency_id' => $currency->getKey(),
            'is_enabled'  => true,
            'starts_at'   => now()->subDay(),
            'ends_at'     => now()->addDays(10),
        ]);
    }

    private function createPriceRecord(): Price
    {
        // Seed a product price with explicit currency links so pricing tables hydrate relationships.
        $product = Product::factory()->create([
            'name' => 'Coverage Product',
            'sku'  => 'COV-PROD-001',
        ]);

        $currency = Currency::factory()->create([
            'code'     => 'CVC',
            'iso_code' => 'CVC-001',
            'name'     => 'Coverage Coin',
            'symbol'   => '¤',
            'is_enabled' => true,
            'is_active'  => true,
        ]);

        return Price::factory()->create([
            'priceable_type' => Product::class,
            'priceable_id'   => $product->getKey(),
            'currency_id'    => $currency->getKey(),
            'amount'         => 199.9900,
            'type'           => 'retail',
            'is_enabled'     => true,
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

    private function createRecommendationConfigSimpleRecord(): RecommendationConfigSimple
    {
        // Produce a simple recommendation config so admin grids expose algorithm metadata.
        return RecommendationConfigSimple::factory()->create([
            'name'           => 'Coverage Config',
            'code'           => 'coverage-config',
            'algorithm_type' => 'collaborative',
            'max_results'    => 5,
            'is_active'      => true,
            'sort_order'     => 1,
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
        // Seed an active referral entry so loyalty monitoring tables expose program progress.
        return Referral::factory()->active()->create([
            'referral_code' => 'COVREF01',
            'status'        => 'active',
            'title'         => [
                'en' => 'Coverage Referral',
                'lt' => 'Draudimo rekomendacija',
            ],
        ]);
    }

    private function createRoleRecord(): Role
    {
        // Register a role so the access-control listing stays covered during migrations to Filament v4.
        return Role::factory()->create([
            'name'       => 'coverage_role',
            'guard_name' => 'web',
        ]);
    }

    private function createSettingRecord(): Setting
    {
        // Persist a general setting so configuration management pages surface deterministic data.
        return Setting::factory()->create([
            'key'          => 'coverage-setting',
            'display_name' => 'Coverage Setting',
            'value'        => 'enabled',
            'type'         => 'string',
            'group'        => 'general',
            'is_public'    => true,
            'is_required'  => false,
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
        // Create a root system setting category so the taxonomy listing exposes a clear example.
        return SystemSettingCategory::factory()->create([
            'name'        => 'Coverage Settings',
            'slug'        => 'coverage-settings',
            'description' => 'Coverage configuration defaults',
            'icon'        => 'heroicon-o-cog-6-tooth',
            'color'       => 'primary',
            'is_active'   => true,
        ]);
    }

    private function createSystemSettingCategoryTranslationRecord(): SystemSettingCategoryTranslation
    {
        // Add an English translation so localization tables demonstrate cross-locale content.
        $category = SystemSettingCategory::factory()->create([
            'name'        => 'Translatable Category',
            'slug'        => 'translatable-category',
            'description' => 'Base category for translations',
            'is_active'   => true,
        ]);

        return SystemSettingCategoryTranslation::factory()->create([
            'system_setting_category_id' => $category->getKey(),
            'locale'                     => 'en',
            'name'                       => 'Coverage Category EN',
            'description'                => 'English coverage category description',
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
        // Link a wishlist to the admin account so tenant-aware scopes still return a visible entry.
        return UserWishlist::factory()->for($this->admin)->public()->create([
            'name'       => 'Coverage Wishlist',
            'is_default' => false,
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
