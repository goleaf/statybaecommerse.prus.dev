<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\ScheduleType;
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
use App\Filament\Resources\EnumResource\Pages\ListEnums;
use App\Filament\Resources\InventoryResource\Pages\ListInventories;
use App\Filament\Resources\LegalResource\Pages\ListLegals;
use App\Filament\Resources\LocationResource\Pages\ListLocations;
use App\Filament\Resources\NewsImageResource\Pages\ListNewsImages as ListNewsImageResourcePage;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages as ListPanelNewsImages;
use App\Filament\Resources\NewsTagResource\Pages\ListNewsTags as ListNewsTagResourcePage;
use App\Filament\Resources\NewsTags\Pages\ListNewsTags as ListPanelNewsTags;
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
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Filament\Resources\SystemSettingCategories\Pages\ListSystemSettingCategories as ListPanelSystemSettingCategories;
use App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories as ListSystemSettingCategoryResourcePage;
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
use App\Models\News;
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
            'audit trails'                             => [ListAuditTrails::class, 'createAuditTrailRecord'],
            'brands'                                   => [ListBrands::class, 'createBrandRecord'],
            'campaign conversions'                     => [ListCampaignConversions::class, 'createCampaignConversionRecord'],
            'campaign schedules'                       => [ListCampaignSchedules::class, 'createCampaignScheduleRecord'],
            'campaigns'                                => [ListCampaigns::class, 'createCampaignRecord'],
            'campaign views'                           => [ListCampaignViews::class, 'createCampaignViewRecord'],
            'cart items'                               => [ListCartItems::class, 'createCartItemRecord'],
            'cities'                                   => [ListCities::class, 'createCityRecord'],
            'collections'                              => [ListCollections::class, 'createCollectionRecord'],
            'collection rules'                         => [ListCollectionRules::class, 'createCollectionRuleRecord'],
            'enum management'                          => [ListEnumManagement::class, 'createEnumValueRecord'],
            'enums'                                    => [ListEnums::class, 'createEnumValueRecord'],
            'inventories'                              => [ListInventories::class, 'createInventoryRecord'],
            'legal documents'                          => [ListLegals::class, 'createLegalRecord'],
            'locations'                                => [ListLocations::class, 'createLocationRecord'],
            'news images'                              => [ListNewsImageResourcePage::class, 'createNewsImageRecord'],
            'news images (panel)'                      => [ListPanelNewsImages::class, 'createNewsImageRecord'],
            'news tags'                                => [ListNewsTagResourcePage::class, 'createNewsTagRecord'],
            'news tags (panel)'                        => [ListPanelNewsTags::class, 'createNewsTagRecord'],
            'posts'                                    => [ListPosts::class, 'createPostRecord'],
            'price list items'                         => [ListPriceListItems::class, 'createPriceListItemRecord'],
            'price lists'                              => [ListPriceLists::class, 'createPriceListRecord'],
            'prices'                                   => [ListPrices::class, 'createPriceRecord'],
            'product variants'                         => [ListProductVariants::class, 'createProductVariantRecord'],
            'recommendation analytics'                 => [ListRecommendationAnalytics::class, 'createRecommendationAnalyticsRecord'],
            'recommendation config simple'             => [ListRecommendationConfigSimples::class, 'createRecommendationConfigSimpleRecord'],
            'referral campaigns'                       => [ListReferralCampaigns::class, 'createReferralCampaignRecord'],
            'referrals'                                => [ListReferrals::class, 'createReferralRecord'],
            'roles'                                    => [ListRoles::class, 'createRoleRecord'],
            'settings'                                 => [ListSettings::class, 'createSettingRecord'],
            'slider translations'                      => [ListSliderTranslations::class, 'createSliderTranslationRecord'],
            'system setting categories'                => [ListSystemSettingCategoryResourcePage::class, 'createSystemSettingCategoryRecord'],
            'system setting categories (panel)'        => [ListPanelSystemSettingCategories::class, 'createSystemSettingCategoryRecord'],
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
        // Provision a campaign so conversion rows can resolve their parent relationship without additional factories.
        $campaign = Campaign::factory()->create([
            'name' => 'Conversion Coverage Campaign',
            'slug' => 'conversion-coverage-campaign',
        ]);

        // Create a customer who will own the conversion to satisfy user-related column rendering.
        $customer = User::factory()->create([
            'name'  => 'Conversion Customer',
            'email' => 'conversion.customer@example.com',
        ]);

        // Persist a conversion entry with deterministic financial metrics to keep table assertions straightforward.
        return CampaignConversion::factory()
            ->for($campaign)
            ->for($customer, 'customer')
            ->create([
                'campaign_name'    => $campaign->name,
                'conversion_type'  => 'purchase',
                'conversion_value' => 199.99,
                'status'           => 'completed',
                'session_id'       => 'coverage-session',
            ]);
    }

    private function createCampaignScheduleRecord(): CampaignSchedule
    {
        // Create a campaign to attach the schedule to so the page can eager load owner details.
        $campaign = Campaign::factory()->create([
            'name' => 'Scheduled Coverage Campaign',
            'slug' => 'scheduled-coverage-campaign',
        ]);

        // Seed a predictable daily schedule that exercises the list columns and filters.
        return CampaignSchedule::factory()
            ->for($campaign)
            ->create([
                'schedule_type'   => ScheduleType::DAILY->value,
                'schedule_config' => [
                    'time'      => '09:00',
                    'timezone'  => 'Europe/Vilnius',
                    'frequency' => 'every_day',
                ],
                'is_active'   => true,
                'next_run_at' => now()->addDay(),
            ]);
    }

    private function createCampaignViewRecord(): CampaignView
    {
        // Link the view to a campaign so aggregate metrics can reference the marketing initiative.
        $campaign = Campaign::factory()->create([
            'name' => 'Coverage View Campaign',
            'slug' => 'coverage-view-campaign',
        ]);

        // Record a recent view tied to the admin user to satisfy the user-owned scope applied in the panel.
        return CampaignView::factory()
            ->for($campaign)
            ->create([
                'customer_id' => $this->admin->getKey(),
                'session_id'  => 'coverage-session',
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'CoverageBrowser/1.0',
                'viewed_at'   => now(),
            ]);
    }

    private function createCartItemRecord(): CartItem
    {
        // Create a product so the cart item can render descriptive snapshot data inside the listing.
        $product = Product::factory()->create([
            'name' => 'Coverage Cart Product',
            'slug' => 'coverage-cart-product',
        ]);

        // Seed a cart item for the authenticated admin user so the user-owned scope includes the row.
        return CartItem::factory()->create([
            'user_id'          => $this->admin->getKey(),
            'product_id'       => $product->getKey(),
            'session_id'       => 'coverage-session',
            'quantity'         => 2,
            'unit_price'       => 49.99,
            'discount_amount'  => 0.0,
            'price'            => 49.99,
            'total_price'      => 99.98,
            'product_snapshot' => [
                'name' => 'Coverage Cart Product',
                'price' => 49.99,
                'sku' => 'CART-COVERAGE',
            ],
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
        // Create a dedicated country and warehouse so stock metrics can display readable location metadata.
        $country = Country::factory()->create([
            'name' => 'Coverage Warehouse Country',
        ]);

        $location = Location::factory()->create([
            'name'        => 'Coverage Warehouse',
            'code'        => 'COV-WH',
            'slug'        => 'coverage-warehouse',
            'type'        => 'warehouse',
            'is_enabled'  => true,
            'address_line_1' => 'Coverage Street 1',
            'city'        => 'Coverage City',
            'country_code'=> $country->cca2,
        ]);

        // Provision a product that the inventory row will reference inside the listing.
        $product = Product::factory()->create([
            'name' => 'Coverage Inventory Product',
            'slug' => 'coverage-inventory-product',
        ]);

        // Persist the inventory record with deterministic SKU and stock values for stable assertions.
        return Inventory::factory()->create([
            'product_id'   => $product->getKey(),
            'warehouse_id' => $location->getKey(),
            'sku'          => 'INV-COV-001',
            'qty'          => 25,
            'meta'         => [
                'reserved'   => 5,
                'incoming'   => 3,
                'threshold'  => 2,
                'is_tracked' => true,
            ],
        ]);
    }

    private function createLegalRecord(): Legal
    {
        // Store an enabled and published legal document so scope filters do not hide the seeded row.
        return Legal::factory()->enabled()->published()->create([
            'key'         => 'coverage-legal',
            'type'        => 'privacy_policy',
            'is_required' => true,
            'sort_order'  => 1,
            'meta_data'   => ['version' => '1.0'],
            'published_at'=> now()->subDay(),
        ]);
    }

    private function createLocationRecord(): Location
    {
        // Prepare a country record so the location passes foreign key constraints during creation.
        $country = Country::factory()->create([
            'name' => 'Coverage Hub Country',
        ]);

        // Persist an enabled warehouse location so the listing can resolve status toggles and addresses.
        return Location::factory()->create([
            'name'        => 'Coverage Hub',
            'code'        => 'COV-HUB',
            'slug'        => 'coverage-hub',
            'type'        => 'warehouse',
            'is_enabled'  => true,
            'city'        => 'Coverage City',
            'country_code'=> $country->cca2,
        ]);
    }

    private function createNewsImageRecord(): NewsImage
    {
        // Create a published news article so the image entry can attach to a visible parent record.
        $news = News::factory()->create([
            'author_name'  => 'Coverage Reporter',
            'published_at' => now()->subDay(),
        ]);

        // Store a featured image with readable metadata to exercise listing columns and filters.
        return NewsImage::factory()
            ->for($news)
            ->create([
                'file_path'   => 'news-images/coverage-image.jpg',
                'alt_text'    => 'Coverage News Image',
                'caption'     => 'Coverage news caption',
                'is_featured' => true,
                'sort_order'  => 1,
            ]);
    }

    private function createNewsTagRecord(): NewsTag
    {
        // Persist a visible tag so tag listings can show localized titles and ordering metadata.
        return NewsTag::factory()->active()->create([
            'name' => 'Coverage Tag',
            'slug' => 'coverage-tag',
            'color' => '#3B82F6',
        ]);
    }

    private function createPriceListItemRecord(): PriceListItem
    {
        // Ensure a price list exists so the item inherits an enabled campaign window and currency.
        $priceList = $this->createPriceListRecord();

        // Provision a variant to anchor the price entry so relational columns hydrate correctly.
        $variant = ProductVariant::factory()->create([
            'name' => 'Coverage Variant Item',
            'sku'  => 'COV-VAR-ITEM',
        ]);

        // Persist the price list item with deterministic pricing to simplify table assertions.
        return PriceListItem::factory()->create([
            'price_list_id'  => $priceList->getKey(),
            'product_id'     => $variant->product_id,
            'variant_id'     => $variant->getKey(),
            'net_amount'     => 79.99,
            'compare_amount' => 99.99,
            'name'           => [
                'en' => 'Coverage Price Item',
                'lt' => 'Coverage Price Item',
            ],
            'is_active' => true,
        ]);
    }

    private function createPriceListRecord(): PriceList
    {
        // Create a reusable currency so linked price lists resolve monetary formatting correctly.
        $currency = Currency::factory()->eur()->create([
            'is_default' => false,
            'code'       => 'EUR',
        ]);

        // Persist an enabled price list within the active date window to satisfy global scopes.
        return PriceList::factory()->create([
            'name'        => 'Coverage Price List',
            'code'        => 'COV-PL',
            'currency_id' => $currency->getKey(),
            'is_enabled'  => true,
            'priority'    => 1,
            'starts_at'   => now()->subDay(),
            'ends_at'     => now()->addDays(7),
        ]);
    }

    private function createPriceRecord(): Price
    {
        // Seed a currency so the price record has a valid foreign key and formatting context.
        $currency = Currency::factory()->eur()->create([
            'is_default' => false,
            'code'       => 'EUR',
        ]);

        // Create a product to attach the polymorphic price entry to for table resolution.
        $product = Product::factory()->create([
            'name' => 'Coverage Price Product',
            'slug' => 'coverage-price-product',
        ]);

        // Persist a price row with compare and cost amounts so pricing badges render predictable values.
        return Price::query()->create([
            'priceable_id'   => $product->getKey(),
            'priceable_type' => $product->getMorphClass(),
            'currency_id'    => $currency->getKey(),
            'amount'         => 59.99,
            'compare_amount' => 69.99,
            'cost_amount'    => 39.99,
            'is_enabled'     => true,
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

    private function createRecommendationConfigSimpleRecord(): RecommendationConfigSimple
    {
        // Persist a simplified recommendation configuration so the streamlined resource lists active presets.
        return RecommendationConfigSimple::factory()->create([
            'name'           => 'Coverage Simple Config',
            'code'           => 'coverage-simple',
            'algorithm_type' => 'collaborative',
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
        // Seed distinct users to act as the referrer and the invited customer for referral listings.
        $referrer = User::factory()->create([
            'name'  => 'Coverage Referrer',
            'email' => 'coverage.referrer@example.com',
        ]);

        $referred = User::factory()->create([
            'name'  => 'Coverage Referred',
            'email' => 'coverage.referred@example.com',
        ]);

        // Persist the referral with translated copy so Filament can display localized columns.
        return Referral::query()->create([
            'referrer_id'            => $referrer->getKey(),
            'referred_id'            => $referred->getKey(),
            'referral_code'          => 'COVERAGE2024',
            'status'                 => 'active',
            'title'                  => ['en' => 'Coverage Referral'],
            'description'            => ['en' => 'Coverage referral description'],
            'benefits_description'   => ['en' => 'Coverage referral benefits'],
            'terms_conditions'       => ['en' => 'Coverage referral terms'],
            'source'                 => 'coverage-tests',
            'campaign'               => 'coverage-campaign',
            'utm_source'             => 'coverage',
            'utm_medium'             => 'tests',
            'utm_campaign'           => 'coverage-suite',
        ]);
    }

    private function createRoleRecord(): Role
    {
        // Persist an administrative role so authorization grids display seeded permissions.
        $role = Role::factory()->create([
            'name'       => 'coverage_manager',
            'guard_name' => 'web',
        ]);

        // Seed a lightweight permissions matrix so computed columns surface deterministic data.
        $role->forceFill([
            'permissions_matrix' => [
                'panel' => ['access' => true],
                'roles' => ['viewAny' => true],
            ],
        ])->save();

        return $role;
    }

    private function createSliderTranslationRecord(): SliderTranslation
    {
        // Persist a slider translation entry to validate the localized slider management grid.
        return SliderTranslation::factory()->english()->create([
            'title' => 'Coverage Slide',
        ]);
    }

    private function createSettingRecord(): Setting
    {
        // Persist an application setting so the simplified settings resource lists a predictable row.
        return Setting::factory()->create([
            'key'          => 'coverage.setting',
            'display_name' => 'Coverage Setting',
            'value'        => 'enabled',
            'type'         => 'string',
            'group'        => 'general',
            'is_public'    => true,
        ]);
    }

    private function createSystemSettingCategoryRecord(): SystemSettingCategory
    {
        // Seed an active system setting category so hierarchical listings render metadata and ordering.
        return SystemSettingCategory::factory()->active()->create([
            'name'       => 'Coverage Category',
            'slug'       => 'coverage-category',
            'color'      => 'primary',
            'icon'       => 'heroicon-o-cog-6-tooth',
            'sort_order' => 1,
        ]);
    }

    private function createSystemSettingCategoryTranslationRecord(): SystemSettingCategoryTranslation
    {
        // Create a base category to associate the translation with for listing hydration.
        $category = SystemSettingCategory::factory()->active()->create([
            'name' => 'Coverage Translation Category',
            'slug' => 'coverage-translation-category',
        ]);

        // Persist an English translation so localized tables display consistent content.
        return SystemSettingCategoryTranslation::factory()
            ->for($category, 'systemSettingCategory')
            ->english()
            ->create([
                'locale'      => 'en',
                'name'        => 'Coverage Category EN',
                'description' => 'Coverage category translation',
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
        // Seed a wishlist for the authenticated admin so the user-owned scope surfaces the record.
        return UserWishlist::factory()->create([
            'user_id'     => $this->admin->getKey(),
            'name'        => 'Coverage Wishlist',
            'description' => 'Coverage wishlist description',
            'is_public'   => true,
            'is_default'  => false,
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
