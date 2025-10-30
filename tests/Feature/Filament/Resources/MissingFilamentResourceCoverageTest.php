<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
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
use App\Filament\Resources\DocumentResource\Pages\ListDocuments;
use App\Filament\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates;
use App\Filament\Resources\EnumManagementResource\Pages\ListEnumManagement;
use App\Filament\Resources\NormalSettingTranslationResource\Pages\ListNormalSettingTranslations;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\PriceListItemResource\Pages\ListPriceListItems;
use App\Filament\Resources\PriceListResource\Pages\ListPriceLists;
use App\Filament\Resources\PriceResource\Pages\ListPrices;
use App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\ListRecommendationAnalytics;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigSimples;
use App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns;
use App\Filament\Resources\VariantCombinationResource\Pages\ListVariantCombinations;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories;
use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages\ListSystemSettingCategoryTranslations;
use App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies;
use App\Filament\Resources\SystemSettingHistoryResource\Pages\ListSystemSettingHistories;
use App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings;
use App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations;
use App\Filament\Resources\SystemSettingsResource\Pages\ListSystemSettings as PanelListSystemSettings;
use App\Filament\Resources\UserManagementResource\Pages\ListUsers;
use App\Filament\Resources\UserPreferenceResource\Pages\ListUserPreferences;
use App\Filament\Resources\UserWishlistResource\Pages\ListUserWishlists;
use App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks;
use App\Models\ActivityLog;
use App\Models\AuditTrail;
use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\CampaignSchedule;
use App\Models\CampaignView;
use App\Models\CartItem;
use App\Models\City;
use App\Models\Collection;
use App\Models\CollectionRule;
use App\Models\Country;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\EnumValue;
use App\Models\NormalSetting;
use App\Models\NormalSettingTranslation;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationConfigSimple;
use App\Models\Referral;
use App\Models\ReferralCampaign;
use App\Models\Role;
use App\Models\SliderTranslation;
use App\Models\Setting;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use App\Models\SystemSettingDependency;
use App\Models\SystemSettingHistory;
use App\Models\SystemSettingTranslation;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\UserProductInteraction;
use App\Models\VariantInventory;
use App\Models\VariantCombination;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'activity logs'             => [ListActivityLogs::class, 'createActivityLogRecord'],
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
            'documents'                 => [ListDocuments::class, 'createDocumentRecord'],
            'document templates'        => [ListDocumentTemplates::class, 'createDocumentTemplateRecord'],
            'enum management'           => [ListEnumManagement::class, 'createEnumValueRecord'],
            'normal setting translations' => [ListNormalSettingTranslations::class, 'createNormalSettingTranslationRecord'],
            'posts'                     => [ListPosts::class, 'createPostRecord'],
            'product variants'          => [ListProductVariants::class, 'createProductVariantRecord'],
            'recommendation analytics'  => [ListRecommendationAnalytics::class, 'createRecommendationAnalyticsRecord'],
            'recommendation simple list'    => [ListRecommendationConfigResourceSimples::class, 'createRecommendationConfigSimpleRecord'],
            'recommendation simple alias'   => [ListRecommendationConfigSimples::class, 'createRecommendationConfigSimpleRecord'],
            'referral campaigns'        => [ListReferralCampaigns::class, 'createReferralCampaignRecord'],
            'referral code usage logs'  => [ListReferralCodeUsageLogs::class, 'createReferralCodeUsageLogRecord'],
            'referral reward logs'      => [ListReferralRewardLogs::class, 'createReferralRewardLogRecord'],
            'slider translations'       => [ListSliderTranslations::class, 'createSliderTranslationRecord'],
            'system setting categories' => [ListSystemSettingCategories::class, 'createSystemSettingCategoryRecord'],
            'system setting category translations' => [ListSystemSettingCategoryTranslations::class, 'createSystemSettingCategoryTranslationRecord'],
            'system setting dependencies' => [ListSystemSettingDependencies::class, 'createSystemSettingDependencyRecord'],
            'system setting histories'  => [ListSystemSettingHistories::class, 'createSystemSettingHistoryRecord'],
            'system settings'           => [ListSystemSettings::class, 'createSystemSettingRecord'],
            'system settings panel alias' => [PanelListSystemSettings::class, 'createSystemSettingRecord'],
            'system setting translations' => [ListSystemSettingTranslations::class, 'createSystemSettingTranslationRecord'],
            'user management'           => [ListUsers::class, 'createUserManagementRecord'],
            'user preferences'          => [ListUserPreferences::class, 'createUserPreferenceRecord'],
            'variant combinations'      => [ListVariantCombinations::class, 'createVariantCombinationRecord'],
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

    private function createActivityLogRecord(): ActivityLog
    {
        // Persist a system activity entry so the audit-style listing resolves a populated row.
        return ActivityLog::factory()->create([
            'log_name'    => 'system',
            'description' => 'Coverage activity log',
        ]);
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

    private function createDocumentRecord(): Document
    {
        // Tie the document to a deterministic template so related columns hydrate consistently.
        $template = DocumentTemplate::factory()->create([
            'name' => 'Coverage Template',
        ]);

        return Document::factory()->for($template, 'template')->draft()->create([
            'title' => 'Coverage Document',
        ]);
    }

    private function createDocumentTemplateRecord(): DocumentTemplate
    {
        // Persist an active invoice template to showcase the documents navigation group.
        return DocumentTemplate::factory()->invoice()->create([
            'name' => 'Coverage Invoice Template',
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

    private function createNormalSettingTranslationRecord(): NormalSettingTranslation
    {
        // Create the base normal setting so the translation points to a persistent configuration entry.
        $setting = NormalSetting::factory()->create([
            'group'        => 'coverage',
            'key'          => 'coverage_setting',
            'locale'       => 'en',
            'type'         => NormalSetting::TYPE_STRING,
            'value'        => 'Coverage value',
            'description'  => 'Coverage description',
            'is_public'    => true,
            'is_encrypted' => false,
            'is_active'    => true,
        ]);

        // Persist the translation with deterministic labels so the table renders predictable text values.
        return NormalSettingTranslation::factory()
            ->forSetting($setting)
            ->forLocale('en')
            ->create([
                'display_name' => 'Coverage Setting',
                'description'  => 'Coverage setting description',
                'help_text'    => 'Coverage help text',
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

    private function createVariantCombinationRecord(): VariantCombination
    {
        // Provision a parent product so the variant combination factory links to a real catalog item.
        $product = Product::factory()->create([
            'name' => 'Coverage Product',
            'slug' => 'coverage-product',
        ]);

        // Store an available combination so the inventory tooling recognises a live configuration row.
        return VariantCombination::factory()
            ->forProduct($product)
            ->withCombination(['color' => 'Crimson', 'size' => 'Large'])
            ->available()
            ->create([
                'combination_hash'      => 'coverage-combination-hash',
                'formatted_combinations' => 'Crimson / Large',
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
        // Create a root category so both navigation grouping and table filters have a tangible record to display.
        return SystemSettingCategory::factory()->create([
            'name' => 'Coverage Settings Category',
            'slug' => 'coverage-settings-category',
        ]);
    }

    private function createSystemSettingCategoryTranslationRecord(): SystemSettingCategoryTranslation
    {
        // Translate an existing category to confirm localized management tables hydrate their relationships correctly.
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Translatable Settings Category',
            'slug' => 'translatable-settings-category',
        ]);

        return SystemSettingCategoryTranslation::factory()
            ->for($category, 'systemSettingCategory')
            ->english()
            ->create([
                'name' => 'Coverage Category Translation',
            ]);
    }

    private function createSystemSettingDependencyRecord(): SystemSettingDependency
    {
        // Establish a dependency between two deterministic settings so the table highlights both the source and prerequisite keys.
        $setting = SystemSetting::factory()->create([
            'key'  => 'coverage-setting-primary',
            'name' => 'Coverage Setting Primary',
        ]);

        $dependsOn = SystemSetting::factory()->create([
            'key'  => 'coverage-setting-prerequisite',
            'name' => 'Coverage Setting Prerequisite',
        ]);

        return SystemSettingDependency::factory()
            ->between($setting, $dependsOn)
            ->equals('enabled')
            ->create([
                'is_active' => true,
            ]);
    }

    private function createSystemSettingHistoryRecord(): SystemSettingHistory
    {
        // Record a history entry against a setting to exercise the audit log columns rendered by the resource.
        $setting = SystemSetting::factory()->create([
            'key'  => 'coverage-setting-historic',
            'name' => 'Coverage Setting Historic',
        ]);

        $admin = User::factory()->create([
            'name'  => 'History Admin',
            'email' => 'history.admin@example.com',
        ]);

        return SystemSettingHistory::factory()
            ->forSetting($setting)
            ->byUser($admin)
            ->withReason('Coverage change verification')
            ->create([
                'old_value' => 'old coverage value',
                'new_value' => 'new coverage value',
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

    private function createSystemSettingTranslationRecord(): SystemSettingTranslation
    {
        // Provide an English translation for a tracked setting to confirm localized columns render deterministic strings.
        $setting = SystemSetting::factory()->create([
            'key'  => 'coverage-setting-translation',
            'name' => 'Coverage Setting Translation',
        ]);

        return SystemSettingTranslation::factory()
            ->for($setting, 'systemSetting')
            ->english()
            ->create([
                'name' => 'Coverage Setting English Translation',
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

    private function createUserProductInteractionRecord(): UserProductInteraction
    {
        // Create an interaction linked to concrete user/product records for table relationship columns.
        return UserProductInteraction::factory()->create([
            'event' => 'coverage-event',
            'meta'  => ['rating' => 3, 'notes' => 'Coverage note'],
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
