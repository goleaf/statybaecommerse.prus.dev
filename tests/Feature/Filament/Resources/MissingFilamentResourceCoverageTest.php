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
use App\Filament\Resources\EnumManagementResource\Pages\ListEnums as ListEnumManagementEnums;
use App\Filament\Resources\EnumResource\Pages\ListEnums as ListEnumResourceEnums;
use App\Filament\Resources\InventoryResource\Pages\ListInventories;
use App\Filament\Resources\LegalResource\Pages\ListLegal;
use App\Filament\Resources\LegalResource\Pages\ListLegals;
use App\Filament\Resources\LocationResource\Pages\ListLocations;
use App\Filament\Resources\NewsImageResource\Pages\ListNewsImages as ListNewsImageResourceImages;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages as ListLegacyNewsImages;
use App\Filament\Resources\NewsTagResource\Pages\ListNewsTags as ListNewsTagResourceTags;
use App\Filament\Resources\NewsTags\Pages\ListNewsTags as ListLegacyNewsTags;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\ListRecommendationAnalytics;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigResourceSimples;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigSimples;
use App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns;
use App\Filament\Resources\ReferralResource\Pages\ListReferrals;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Filament\Resources\SystemSettingCategories\Pages\ListSystemSettingCategories as ListLegacySystemSettingCategories;
use App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories;
use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages\ListSystemSettingCategoryTranslations;
use App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings;
use App\Filament\Resources\UserManagementResource\Pages\ListUsers;
use App\Filament\Resources\UserPreferenceResource\Pages\ListUserPreferences;
use App\Filament\Resources\UserWishlistResource\Pages\ListUserWishlists;
use App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks;
use App\Models\AuditTrail;
use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\CampaignSchedule;
use App\Models\CampaignView;
use App\Models\Brand;
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
use App\Models\Price;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Post;
use App\Models\ProductVariant;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationConfigSimple;
use App\Models\ReferralCampaign;
use App\Models\Referral;
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
            'audit trails'              => [ListAuditTrails::class, 'createAuditTrailRecord'],
            'brands'                    => [ListBrands::class, 'createBrandRecord'],
            'campaigns'                 => [ListCampaigns::class, 'createCampaignRecord'],
            'campaign conversions'      => [ListCampaignConversions::class, 'createCampaignConversionRecord'],
            'campaign schedules'        => [ListCampaignSchedules::class, 'createCampaignScheduleRecord'],
            'campaign views'            => [ListCampaignViews::class, 'createCampaignViewRecord'],
            'cart items'                => [ListCartItems::class, 'createCartItemRecord'],
            'cities'                    => [ListCities::class, 'createCityRecord'],
            'collections'               => [ListCollections::class, 'createCollectionRecord'],
            'collection rules'          => [ListCollectionRules::class, 'createCollectionRuleRecord'],
            'enum management'           => [ListEnumManagement::class, 'createEnumValueRecord'],
            'enum management (list enums)' => [ListEnumManagementEnums::class, 'createEnumValueRecord'],
            'enum resource (list enums)'   => [ListEnumResourceEnums::class, 'createEnumValueRecord'],
            'inventory'                 => [ListInventories::class, 'createInventoryRecord'],
            'legal single list'         => [ListLegal::class, 'createLegalRecord'],
            'legal widget tabs'         => [ListLegals::class, 'createLegalRecord'],
            'locations'                 => [ListLocations::class, 'createLocationRecord'],
            'news images (resource)'    => [ListNewsImageResourceImages::class, 'createNewsImageRecord'],
            'news images (legacy)'      => [ListLegacyNewsImages::class, 'createNewsImageRecord'],
            'news tags (resource)'      => [ListNewsTagResourceTags::class, 'createNewsTagRecord'],
            'news tags (legacy)'        => [ListLegacyNewsTags::class, 'createNewsTagRecord'],
            'posts'                     => [ListPosts::class, 'createPostRecord'],
            'product variants'          => [ListProductVariants::class, 'createProductVariantRecord'],
            'recommendation analytics'  => [ListRecommendationAnalytics::class, 'createRecommendationAnalyticsRecord'],
            'recommendation simple list'    => [ListRecommendationConfigResourceSimples::class, 'createRecommendationConfigSimpleRecord'],
            'recommendation simple alias'   => [ListRecommendationConfigSimples::class, 'createRecommendationConfigSimpleRecord'],
            'referral campaigns'        => [ListReferralCampaigns::class, 'createReferralCampaignRecord'],
            'referrals'                 => [ListReferrals::class, 'createReferralRecord'],
            'roles'                     => [ListRoles::class, 'createRoleRecord'],
            'system setting categories' => [ListSystemSettingCategories::class, 'createSystemSettingCategoryRecord'],
            'system setting categories (legacy namespace)' => [ListLegacySystemSettingCategories::class, 'createSystemSettingCategoryRecord'],
            'system setting category translations' => [ListSystemSettingCategoryTranslations::class, 'createSystemSettingCategoryTranslationRecord'],
            'slider translations'       => [ListSliderTranslations::class, 'createSliderTranslationRecord'],
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

    private function createCampaignConversionRecord(): CampaignConversion
    {
        // Link the conversion back to a known campaign so relation columns hydrate correctly in the grid.
        $campaign = Campaign::factory()->create([
            'name' => 'Coverage Conversion Campaign',
            'slug' => 'coverage-conversion-campaign',
        ]);

        // Persist a completed conversion with deterministic monetary values for predictable assertions.
        return CampaignConversion::factory()
            ->for($campaign)
            ->create([
                'conversion_type'  => 'purchase',
                'conversion_value' => 199.50,
                'status'           => 'completed',
            ]);
    }

    private function createCampaignScheduleRecord(): CampaignSchedule
    {
        // Ensure the schedule attaches to a campaign so the Filament table can eager load the relationship without errors.
        $campaign = Campaign::factory()->create([
            'name' => 'Coverage Schedule Campaign',
            'slug' => 'coverage-schedule-campaign',
        ]);

        // Store a simple daily schedule so the grid showcases upcoming run metadata.
        return CampaignSchedule::factory()
            ->for($campaign)
            ->create([
                'schedule_type'   => 'daily',
                'schedule_config' => [
                    'time'      => '09:00',
                    'timezone'  => 'Europe/Vilnius',
                    'frequency' => 'every_day',
                ],
            ]);
    }

    private function createCampaignViewRecord(): CampaignView
    {
        // Create a campaign to connect the view so table columns rendering campaign details stay populated.
        $campaign = Campaign::factory()->create([
            'name' => 'Coverage View Campaign',
            'slug' => 'coverage-view-campaign',
        ]);

        // Persist a recent view to guarantee the Livewire table surfaces the entry immediately.
        return CampaignView::factory()
            ->for($campaign)
            ->create([
                'session_id' => 'coverage-session',
                'ip_address' => '192.0.2.10',
            ]);
    }

    private function createCartItemRecord(): CartItem
    {
        // Generate a cart item with a concrete quantity so subtotal calculations appear consistent inside the listing.
        return CartItem::factory()->create([
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
        // Seed a tracked inventory row so quantity-based columns render meaningful values in the listing.
        return Inventory::factory()->inStock()->create([
            'sku' => 'INV-COVERAGE-001',
        ]);
    }

    private function createLegalRecord(): Legal
    {
        // Persist an enabled legal document so the widget-tab driven listings surface a visible entry.
        if ($existing = Legal::query()->where('key', 'coverage-terms')->first()) {
            return $existing;
        }

        return Legal::factory()->create([
            'key'         => 'coverage-terms',
            'type'        => 'terms_of_use',
            'is_enabled'  => true,
            'is_required' => false,
        ]);
    }

    private function createLocationRecord(): Location
    {
        // Create a warehouse location to satisfy inventory-dependent relationships and provide predictable labels.
        return Location::factory()->warehouse()->create([
            'code' => 'WH-COVERAGE',
            'name' => 'Coverage Warehouse',
        ]);
    }

    private function createNewsImageRecord(): NewsImage
    {
        // Store a news image linked to a news article so the gallery grids showcase a concrete asset.
        if ($existing = NewsImage::query()->where('file_path', 'news-images/coverage-image.jpg')->first()) {
            return $existing;
        }

        return NewsImage::factory()->create([
            'file_path' => 'news-images/coverage-image.jpg',
        ]);
    }

    private function createNewsTagRecord(): NewsTag
    {
        // Generate a visible tag so both legacy and resource namespaces render the same taxonomy entry.
        if ($existing = NewsTag::query()->where('slug', 'coverage-tag')->first()) {
            return $existing;
        }

        return NewsTag::factory()->create([
            'name' => 'Coverage Tag',
            'slug' => 'coverage-tag',
        ]);
    }

    private function createPriceListRecord(): PriceList
    {
        // Persist a default price list so the resource table can resolve currency and scheduling metadata.
        if ($existing = PriceList::query()->where('code', 'PL-COVERAGE')->first()) {
            return $existing;
        }

        return PriceList::factory()->default()->create([
            'name' => 'Coverage Price List',
            'code' => 'PL-COVERAGE',
        ]);
    }

    private function createPriceListItemRecord(): PriceListItem
    {
        // Attach a price list item to the seeded list so item-level listings display deterministic pricing rows.
        $priceList = $this->createPriceListRecord();

        return PriceListItem::factory()->for($priceList)->create([
            'net_amount' => 79.99,
        ]);
    }

    private function createPriceRecord(): Price
    {
        // Ensure a currency exists so the price entry renders without referencing a missing foreign key.
        $currency = Currency::factory()->eur()->create([
            'code' => 'EUR',
        ]);

        // Persist a simple price row linked to a product for the pricing resource listing.
        return Price::factory()->create([
            'currency_id' => $currency->getKey(),
            'amount'       => 129.99,
        ]);
    }

    private function createRecommendationConfigSimpleRecord(): RecommendationConfigSimple
    {
        // Seed a configuration profile so both simple recommendation list pages can hydrate the dataset.
        if ($existing = RecommendationConfigSimple::query()->where('code', 'coverage-recs')->first()) {
            return $existing;
        }

        return RecommendationConfigSimple::factory()->create([
            'name' => 'Coverage Recommendation Profile',
            'code' => 'coverage-recs',
        ]);
    }

    private function createReferralRecord(): Referral
    {
        // Pair a referrer and referred user so the referrals table exposes relational columns without nulls.
        $referrer = User::factory()->create(['email' => 'referrer@example.com']);
        $referred = User::factory()->create(['email' => 'referred@example.com']);

        return Referral::factory()->create([
            'referrer_id'   => $referrer->getKey(),
            'referred_id'   => $referred->getKey(),
            'referral_code' => 'COVERAGE-REF',
            'status'        => 'pending',
            'title'         => 'Coverage Referral',
        ]);
    }

    private function createRoleRecord(): Role
    {
        // Create a role within the web guard so the role management grid has a concrete entry to display.
        return Role::factory()->create([
            'name' => 'coverage_role',
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

    private function createSystemSettingCategoryRecord(): SystemSettingCategory
    {
        // Persist a root category so both legacy and v4 namespaces can resolve hierarchical metadata.
        if ($existing = SystemSettingCategory::query()->where('slug', 'coverage-category')->first()) {
            return $existing;
        }

        return SystemSettingCategory::factory()->create([
            'name' => 'Coverage Category',
            'slug' => 'coverage-category',
        ]);
    }

    private function createSystemSettingCategoryTranslationRecord(): SystemSettingCategoryTranslation
    {
        // Reuse the seeded category to ensure the translation resource points at an existing parent row.
        $category = $this->createSystemSettingCategoryRecord();

        // Provide an English translation so locale-specific columns appear in the listing.
        if ($existing = SystemSettingCategoryTranslation::query()
            ->where('system_setting_category_id', $category->getKey())
            ->where('locale', 'en')
            ->first()) {
            return $existing;
        }

        return SystemSettingCategoryTranslation::factory()
            ->for($category, 'systemSettingCategory')
            ->english()
            ->create([
                'name'        => 'Coverage Category',
                'description' => 'Coverage category description',
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
        // Seed a wishlist linked to a customer so the Filament table can render ownership details.
        return UserWishlist::factory()->create([
            'name' => 'Coverage Wishlist',
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
