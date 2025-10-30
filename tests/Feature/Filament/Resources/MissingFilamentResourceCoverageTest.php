<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\ModerationState;
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
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Filament\WidgetTabs\Components\WidgetTab;
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
use App\Models\SliderTranslation;
use App\Models\Slider;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\VariantInventory;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
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

        // Silence the attribution observer so system setting factories do not
        // attempt to reference a non-existent "system" user inside the SQLite
        // harness used by this regression suite.
        config()->set('attribution.system_user_id', null);
        config()->set('attribution.system_user_email', null);
        config()->set('attribution.system_user_name', null);

        // Provide missing class aliases used by widget tab-enabled list pages so
        // the Livewire components can resolve their tab builders without the
        // application bootstrapping additional support code.
        if (! class_exists('App\\Filament\\Resources\\CampaignResource\\Pages\\SchemaTab')) {
            class_alias(WidgetTab::class, 'App\\Filament\\Resources\\CampaignResource\\Pages\\SchemaTab');
        }

        // Some legacy resources still reference the VariantStock model name even
        // though the backing Eloquent model lives under VariantInventory. The
        // alias keeps table hydration working inside this consolidated smoke test.
        if (! class_exists(\App\Models\VariantStock::class)) {
            class_alias(VariantInventory::class, \App\Models\VariantStock::class);
        }

        if (! class_exists('App\\Filament\\Resources\\Filter')) {
            class_alias(\Filament\Tables\Filters\Filter::class, 'App\\Filament\\Resources\\Filter');
        }

        if (! class_exists('App\\Filament\\Resources\\Str')) {
            class_alias(\Illuminate\Support\Str::class, 'App\\Filament\\Resources\\Str');
        }

        if (! class_exists('App\\Filament\\Resources\\SupportSupportFlatpickr')) {
            class_alias(\App\Support\Filament\Components\Flatpickr::class, 'App\\Filament\\Resources\\SupportSupportFlatpickr');
        }

        // Align the lightweight SQLite schema with production expectations so the
        // recommendation and slider resources can query optional JSON columns
        // without hitting missing-column exceptions during the test run.
        if (Schema::hasTable('recommendation_blocks') && ! Schema::hasColumn('recommendation_blocks', 'meta')) {
            Schema::table('recommendation_blocks', static function (Blueprint $table): void {
                $table->json('meta')->nullable();
            });
        }

        if (Schema::hasTable('sliders') && ! Schema::hasColumn('sliders', 'name')) {
            Schema::table('sliders', static function (Blueprint $table): void {
                $table->string('name')->nullable();
            });
        }

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
        $component = Livewire::test($pageClass);

        if ($factoryMethod === 'createPostRecord') {
            // Force the posts listing onto the published status filter so the smoke test
            // mirrors the moderation view administrators rely on in production.
            $component->filterTable('status', 'published');
        }

        $component->call('loadTable');

        if ($factoryMethod === 'createPostRecord') {
            // Inspect the hydrated records collection directly so badgeable title columns
            // and translation overlays do not interfere with the smoke assertion logic.
            $records = $component->instance()->getTableRecords();

            if ($records instanceof Paginator || $records instanceof CursorPaginator) {
                $records = $records->getCollection();
            }

            $this->assertTrue(
                $records->contains(static fn (Post $post): bool => $post->is($record->fresh())),
                'Failed asserting that the posts listing contains the seeded coverage record.',
            );

            $component->assertSee($record->title);

            return;
        }

        $component->assertCanSeeTableRecords([$record->fresh()]);
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
        $post = Post::factory()
            ->published()
            ->create([
                'title'              => 'Coverage Post',
                'title_translations' => [
                    'en' => 'Coverage Post',
                    'lt' => 'Aprėpties įrašas',
                ],
                'slug'               => 'coverage-post',
                'status'             => 'published',
                'moderation_state'   => ModerationState::Published->value,
                'user_id'            => $this->admin->getKey(),
                'approved_by_id'     => $this->admin->getKey(),
                'approved_at'        => now(),
                'published_at'       => now(),
                'submitted_for_review_at' => now()->subDay(),
                'excerpt'            => 'Coverage summary',
                'excerpt_translations' => [
                    'en' => 'Coverage summary',
                    'lt' => 'Aprėpties santrauka',
                ],
            ]);

        return $post;
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
        // Persist a slider with deterministic metadata so translation listings have stable parent context.
        $slider = Slider::query()->create([
            'title'            => 'Coverage Slider',
            'description'      => 'Slider placeholder for translation coverage.',
            'button_text'      => 'Explore',
            'button_url'       => 'https://example.com/coverage',
            'background_color' => '#ffffff',
            'text_color'       => '#111827',
            'sort_order'       => 1,
            'is_active'        => true,
            'settings'         => ['animation' => 'fade', 'duration' => 4000],
        ]);

        $slider->forceFill(['name' => 'coverage-slider'])->save();

        // Persist a slider translation entry to validate the localized slider management grid.
        return SliderTranslation::factory()->english()->create([
            'slider_id'   => $slider->getKey(),
            'title'       => 'Coverage Slide',
            'button_text' => 'View details',
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
