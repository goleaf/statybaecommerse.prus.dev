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
use App\Filament\Resources\EnumResource\Pages\ListEnums;
use App\Filament\Resources\EnumManagementResource\Pages\ListEnumManagement;
use App\Filament\Resources\InventoryResource\Pages\ListInventories;
use App\Filament\Resources\LegalResource\Pages\ListLegal;
use App\Filament\Resources\LegalResource\Pages\ListLegals;
use App\Filament\Resources\LocationResource\Pages\ListLocations;
use App\Filament\Resources\NewsImageResource\Pages\ListNewsImages;
use App\Filament\Resources\NewsTagResource\Pages\ListNewsTags;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\ListRecommendationAnalytics;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigResourceSimples;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigSimples;
use App\Filament\Resources\ReferralResource\Pages\ListReferrals;
use App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Filament\Resources\SettingResource\Pages\ListSettings;
use App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories;
use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages\ListSystemSettingCategoryTranslations;
use App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings;
use App\Filament\Resources\UserManagementResource\Pages\ListUsers;
use App\Filament\Resources\UserPreferenceResource\Pages\ListUserPreferences;
use App\Filament\Resources\UserWishlistResource\Pages\ListUserWishlists;
use App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks;
use App\Models\AuditTrail;
use App\Models\Brand;
use App\Models\CampaignConversion;
use App\Models\Campaign;
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
use App\Models\ReferralCampaign;
use App\Models\Referral;
use App\Models\Role;
use App\Models\SliderTranslation;
use App\Models\Setting;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use App\Models\SystemSetting;
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
            'audit trails'                             => [ListAuditTrails::class, 'createAuditTrailRecord'],
            'brands'                                   => [ListBrands::class, 'createBrandRecord'],
            'campaign conversions'                     => [ListCampaignConversions::class, 'createCampaignConversionRecord'],
            'campaign schedules'                       => [ListCampaignSchedules::class, 'createCampaignScheduleRecord'],
            'campaign views'                           => [ListCampaignViews::class, 'createCampaignViewRecord'],
            'campaigns'                                => [ListCampaigns::class, 'createCampaignRecord'],
            'cart items'                               => [ListCartItems::class, 'createCartItemRecord'],
            'cities'                                   => [ListCities::class, 'createCityRecord'],
            'collections'                              => [ListCollections::class, 'createCollectionRecord'],
            'collection rules'                         => [ListCollectionRules::class, 'createCollectionRuleRecord'],
            'enum management'                          => [ListEnumManagement::class, 'createEnumValueRecord'],
            'enums'                                    => [ListEnums::class, 'createEnumValueRecord'],
            'inventories'                              => [ListInventories::class, 'createInventoryRecord'],
            'legal overview'                           => [ListLegal::class, 'createLegalRecord'],
            'legal widget tabs'                        => [ListLegals::class, 'createLegalRecord'],
            'locations'                                => [ListLocations::class, 'createLocationRecord'],
            'news images'                              => [ListNewsImages::class, 'createNewsImageRecord'],
            'news tags'                                => [ListNewsTags::class, 'createNewsTagRecord'],
            'posts'                                    => [ListPosts::class, 'createPostRecord'],
            'price list items'                         => [ListPriceListItems::class, 'createPriceListItemRecord'],
            'price lists'                              => [ListPriceLists::class, 'createPriceListRecord'],
            'prices'                                   => [ListPrices::class, 'createPriceRecord'],
            'product variants'                         => [ListProductVariants::class, 'createProductVariantRecord'],
            'recommendation analytics'                 => [ListRecommendationAnalytics::class, 'createRecommendationAnalyticsRecord'],
            'recommendation config resource simples'   => [ListRecommendationConfigResourceSimples::class, 'createRecommendationConfigSimpleRecord'],
            'recommendation config simples'            => [ListRecommendationConfigSimples::class, 'createRecommendationConfigSimpleRecord'],
            'referral campaigns'                       => [ListReferralCampaigns::class, 'createReferralCampaignRecord'],
            'referrals'                                => [ListReferrals::class, 'createReferralRecord'],
            'roles'                                    => [ListRoles::class, 'createRoleRecord'],
            'settings'                                 => [ListSettings::class, 'createSettingRecord'],
            'slider translations'                      => [ListSliderTranslations::class, 'createSliderTranslationRecord'],
            'system setting categories'                => [ListSystemSettingCategories::class, 'createSystemSettingCategoryRecord'],
            'system setting category translations'     => [ListSystemSettingCategoryTranslations::class, 'createSystemSettingCategoryTranslationRecord'],
            'system settings'                          => [ListSystemSettings::class, 'createSystemSettingRecord'],
            'user management'                          => [ListUsers::class, 'createUserManagementRecord'],
            'user preferences'                         => [ListUserPreferences::class, 'createUserPreferenceRecord'],
            'user wishlists'                           => [ListUserWishlists::class, 'createUserWishlistRecord'],
            'variant stock'                            => [ListVariantStocks::class, 'createVariantInventoryRecord'],
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
        // Seed a completed conversion so performance dashboards expose tangible revenue metrics.
        return CampaignConversion::factory()->create([
            'conversion_type'  => 'purchase',
            'conversion_value' => 249.95,
            'status'           => 'completed',
        ]);
    }

    private function createCampaignScheduleRecord(): CampaignSchedule
    {
        // Generate a daily schedule to exercise next-run indicators within the automation planner grid.
        return CampaignSchedule::factory()->daily()->create([
            'is_active' => true,
        ]);
    }

    private function createCampaignViewRecord(): CampaignView
    {
        // Persist a tracked campaign view so visitor analytics can surface customer, IP, and referer data.
        return CampaignView::factory()->withCustomer()->create([
            'ip_address' => '203.0.113.10',
        ]);
    }

    private function createCartItemRecord(): CartItem
    {
        // Create a priced cart line item to populate basket summaries with deterministic quantity math.
        return CartItem::factory()->create([
            'quantity'   => 2,
            'unit_price' => 49.99,
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

    private function createInventoryRecord(): Inventory
    {
        // Provision a tracked warehouse item so the inventory list highlights SKU and stock counts.
        return Inventory::factory()->tracked()->create([
            'sku' => 'COV-INV-001',
            'qty' => 42,
        ]);
    }

    private function createLegalRecord(): Legal
    {
        // Seed an enabled legal document to populate compliance listings with a familiar policy key.
        return Legal::factory()->enabled()->create([
            'key'  => 'coverage-policy',
            'type' => 'privacy_policy',
        ]);
    }

    private function createLocationRecord(): Location
    {
        // Create a warehouse location so logistics tables can display a friendly code and city pair.
        return Location::factory()->warehouse()->create([
            'code' => 'COV-WH-1',
            'name' => 'Coverage Warehouse',
        ]);
    }

    private function createNewsImageRecord(): NewsImage
    {
        // Store a featured news image to verify media galleries render captions and alt text entries.
        return NewsImage::factory()->featured()->create([
            'file_path' => 'news-images/coverage-image.jpg',
            'alt_text'  => 'Coverage campaign hero image',
        ]);
    }

    private function createNewsTagRecord(): NewsTag
    {
        // Persist a visible news tag so tagging interfaces have deterministic names and colours to display.
        return NewsTag::factory()->active()->create([
            'name' => 'Coverage Updates',
            'slug' => 'coverage-updates',
        ]);
    }

    private function createPriceListRecord(): PriceList
    {
        // Generate an enabled price list to ensure commerce pricing tables expose schedule metadata.
        return PriceList::factory()->create([
            'name' => 'Coverage Price List',
            'code' => 'COV-PL-001',
        ]);
    }

    private function createPriceListItemRecord(): PriceListItem
    {
        // Attach a translatable price list item so tiered pricing grids have meaningful labels.
        return PriceListItem::factory()->create([
            'name' => [
                'en' => 'Coverage Tier',
                'lt' => 'Coverage Tier',
            ],
        ]);
    }

    private function createPriceRecord(): Price
    {
        // Register a retail product price with a dedicated currency to populate financial summaries.
        $currency = Currency::factory()->create([
            'code' => 'COV',
            'name' => 'Coverage Currency',
        ]);

        $product = Product::factory()->create([
            'name' => 'Coverage Pricing Product',
            'slug' => 'coverage-pricing-product',
        ]);

        return Price::factory()->create([
            'currency_id'   => $currency->getKey(),
            'priceable_type' => $product->getMorphClass(),
            'priceable_id'   => $product->getKey(),
            'amount'         => 199.99,
            'compare_amount' => 249.99,
            'type'           => 'retail',
        ]);
    }

    private function createRecommendationConfigSimpleRecord(): RecommendationConfigSimple
    {
        // Seed a recommendation config so optimisation dashboards can render algorithm codes and names.
        return RecommendationConfigSimple::factory()->create([
            'name' => 'Coverage Config',
            'code' => 'coverage-config',
        ]);
    }

    private function createReferralRecord(): Referral
    {
        // Create an in-progress referral so the referral hub surfaces code, status, and metadata details.
        return Referral::factory()->pending()->create([
            'referral_code' => 'COVERAGE24',
        ]);
    }

    private function createRoleRecord(): Role
    {
        // Provision an application role to confirm the permissions matrix renders seeded authorities.
        return Role::factory()->create([
            'name' => 'coverage_role',
        ]);
    }

    private function createSettingRecord(): Setting
    {
        // Persist a simple configuration value so the settings table lists deterministic key/group data.
        return Setting::factory()->create([
            'key'          => 'coverage.setting',
            'display_name' => 'Coverage Setting',
            'type'         => 'string',
            'group'        => 'general',
        ]);
    }

    private function createSystemSettingCategoryRecord(): SystemSettingCategory
    {
        // Add a system setting category so administrators see organised configuration groupings.
        return SystemSettingCategory::factory()->create([
            'name' => 'Coverage Category',
            'slug' => 'coverage-category',
        ]);
    }

    private function createSystemSettingCategoryTranslationRecord(): SystemSettingCategoryTranslation
    {
        // Seed an English translation to guarantee localisation tables hydrate with readable labels.
        return SystemSettingCategoryTranslation::factory()->english()->create([
            'name'        => 'Coverage Category',
            'description' => 'Coverage configuration category',
        ]);
    }

    private function createUserWishlistRecord(): UserWishlist
    {
        // Generate a public wishlist so merchandising widgets can reference named customer collections.
        return UserWishlist::factory()->public()->create([
            'name' => 'Coverage Wishlist',
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
}
