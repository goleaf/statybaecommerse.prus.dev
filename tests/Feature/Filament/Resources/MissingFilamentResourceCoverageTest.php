<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\AuditTrailResource\Pages\ListAuditTrails;
use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Filament\Resources\CampaignResource\Pages\ListCampaigns;
use App\Filament\Resources\CityResource\Pages\ListCities;
use App\Filament\Resources\CollectionResource\Pages\ListCollections;
use App\Filament\Resources\CollectionRuleResource\Pages\ListCollectionRules;
use App\Filament\Resources\EnumManagementResource\Pages\ListEnumManagement;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\ListRecommendationAnalytics;
use App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns;
use App\Filament\Resources\ReferralCodeResource\Pages\ListReferralCodes;
use App\Filament\Resources\ReferralCodeStatisticsResource\Pages\ListReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs;
use App\Filament\Resources\ReferralRewardLogResource\Pages\ListReferralRewardLogs;
use App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards;
use App\Filament\Resources\ReferralStatisticsResource\Pages\ListReferralStatistics;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings;
use App\Filament\Resources\UserManagementResource\Pages\ListUsers;
use App\Filament\Resources\UserPreferenceResource\Pages\ListUserPreferences;
use App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks;
use App\Models\AuditTrail;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\City;
use App\Models\Collection;
use App\Models\CollectionRule;
use App\Models\Country;
use App\Models\EnumValue;
use App\Models\Post;
use App\Models\ProductVariant;
use App\Models\RecommendationAnalytics;
use App\Models\ReferralCampaign;
use App\Models\ReferralCode;
use App\Models\ReferralCodeStatistics;
use App\Models\ReferralCodeUsageLog;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\ReferralStatistics;
use App\Models\SliderTranslation;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserPreference;
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
            'cities'                    => [ListCities::class, 'createCityRecord'],
            'collections'               => [ListCollections::class, 'createCollectionRecord'],
            'collection rules'          => [ListCollectionRules::class, 'createCollectionRuleRecord'],
            'enum management'           => [ListEnumManagement::class, 'createEnumValueRecord'],
            'posts'                     => [ListPosts::class, 'createPostRecord'],
            'product variants'          => [ListProductVariants::class, 'createProductVariantRecord'],
            'recommendation analytics'  => [ListRecommendationAnalytics::class, 'createRecommendationAnalyticsRecord'],
            'referral campaigns'        => [ListReferralCampaigns::class, 'createReferralCampaignRecord'],
            'referral codes'            => [ListReferralCodes::class, 'createReferralCodeRecord'],
            'referral code statistics'  => [ListReferralCodeStatistics::class, 'createReferralCodeStatisticsRecord'],
            'referral code usage logs'  => [ListReferralCodeUsageLogs::class, 'createReferralCodeUsageLogRecord'],
            'referral rewards'          => [ListReferralRewards::class, 'createReferralRewardRecord'],
            'referral reward logs'      => [ListReferralRewardLogs::class, 'createReferralRewardLogRecord'],
            'referral statistics'       => [ListReferralStatistics::class, 'createReferralStatisticsRecord'],
            'slider translations'       => [ListSliderTranslations::class, 'createSliderTranslationRecord'],
            'system settings'           => [ListSystemSettings::class, 'createSystemSettingRecord'],
            'user management'           => [ListUsers::class, 'createUserManagementRecord'],
            'user preferences'          => [ListUserPreferences::class, 'createUserPreferenceRecord'],
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

    private function createReferralCodeRecord(): ReferralCode
    {
        // Issue an active referral code so marketing admins can see a predictable listing row.
        $owner = User::factory()->create([
            'email' => 'referral.code.owner@example.com',
        ]);

        return ReferralCode::factory()->forUser($owner)->create([
            'code'      => 'COVERC',
            'is_active' => true,
            'metadata'  => ['generated_via' => 'coverage'],
        ]);
    }

    private function createReferralCodeStatisticsRecord(): ReferralCodeStatistics
    {
        // Pair statistics with a concrete referral code so aggregated metrics resolve cleanly.
        $referralCode = $this->createReferralCodeRecord();

        return ReferralCodeStatistics::factory()->withReferralCode($referralCode)->create([
            'date'              => now()->toDateString(),
            'total_views'       => 125,
            'total_clicks'      => 40,
            'total_signups'     => 15,
            'total_conversions' => 6,
            'total_revenue'     => 420.50,
            'metadata'          => ['source' => 'coverage'],
        ]);
    }

    private function createReferralCodeUsageLogRecord(): ReferralCodeUsageLog
    {
        // Record a deterministic referral code usage event to hydrate behavioural analytics tables.
        $user = User::factory()->create([
            'email' => 'referral.usage@example.com',
        ]);

        $referralCode = $this->createReferralCodeRecord();

        return ReferralCodeUsageLog::factory()
            ->withUser($user)
            ->withReferralCode($referralCode)
            ->create([
                'ip_address' => '203.0.113.10',
                'user_agent' => 'Coverage Browser',
                'referrer'   => 'https://coverage.example.com',
                'metadata'   => ['medium' => 'email'],
            ]);
    }

    private function createSliderTranslationRecord(): SliderTranslation
    {
        // Persist a slider translation entry to validate the localized slider management grid.
        return SliderTranslation::factory()->english()->create([
            'title' => 'Coverage Slide',
        ]);
    }

    private function createReferralRewardRecord(): ReferralReward
    {
        // Seed a localized referral reward so reward management listings show a tangible incentive.
        $recipient = User::factory()->create([
            'email' => 'referral.reward@example.com',
        ]);

        return ReferralReward::factory()->forUser($recipient)->create([
            'type'        => 'discount',
            'title'       => [
                'en' => 'Coverage Reward',
                'lt' => 'Draudimo atlygis',
            ],
            'description' => [
                'en' => 'Coverage reward description',
                'lt' => 'Padengimo atlygio aprašas',
            ],
            'amount'        => 25.00,
            'currency_code' => 'EUR',
            'status'        => 'pending',
            'is_active'     => true,
            'reward_data'   => [
                'category'             => 'discount',
                'discount_percentage'  => 15,
            ],
        ]);
    }

    private function createReferralRewardLogRecord(): ReferralRewardLog
    {
        // Capture the lifecycle log for a seeded reward so audit tables reflect recent activity.
        $reward = $this->createReferralRewardRecord();

        $actor = User::factory()->create([
            'email' => 'referral.reward.actor@example.com',
        ]);

        return ReferralRewardLog::factory()
            ->for($reward, 'referralReward')
            ->for($actor)
            ->create([
                'action'     => ReferralRewardLog::ACTION_EARNED,
                'data'       => [
                    'amount'      => 25.00,
                    'currency'    => 'EUR',
                    'reward_type' => 'discount',
                    'earned_at'   => now()->toISOString(),
                ],
                'ip_address' => '198.51.100.10',
                'user_agent' => 'Coverage Reward Agent',
            ]);
    }

    private function createReferralStatisticsRecord(): ReferralStatistics
    {
        // Summarise referral performance so reporting dashboards render aggregate metrics.
        $user = User::factory()->create([
            'email' => 'referral.statistics@example.com',
        ]);

        return ReferralStatistics::factory()->for($user)->create([
            'date'                  => now()->toDateString(),
            'total_referrals'       => 12,
            'completed_referrals'   => 8,
            'pending_referrals'     => 4,
            'total_rewards_earned'  => 310.75,
            'total_discounts_given' => 185.25,
            'metadata'              => ['note' => 'coverage snapshot'],
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
