<?php

declare(strict_types=1);

namespace Tests\Filament;

use App\Filament\Resources\AuditTrailResource;
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CampaignResource;
use App\Filament\Resources\CityResource;
use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\CollectionRuleResource;
use App\Filament\Resources\EnumManagementResource;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\ProductVariantResource;
use App\Filament\Resources\RecommendationAnalyticsResource;
use App\Filament\Resources\ReferralCampaignResource;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\SliderTranslationResource;
use App\Filament\Resources\SystemSettingResource;
use App\Filament\Resources\UserManagementResource;
use App\Filament\Resources\UserPreferenceResource;
use App\Filament\Resources\VariantStockResource;
use App\Models\AuditTrail;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\City;
use App\Models\Collection;
use App\Models\CollectionRule;
use App\Models\EnumValue;
use App\Models\Post;
use App\Models\ProductVariant;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\ReferralCampaign;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\SliderTranslation;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\VariantInventory;
use Filament\Schemas\Components\Tabs\Tab as SchemaTabComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Filament\Resources\Resource;
use Livewire\Livewire;
use Tests\TestCase;

final class MissingResourceSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the admin panel before mounting Livewire components so Filament v4 services register correctly.
        $this->resolveAdminPanel();

        // Normalise locale-dependent output to English to avoid brittle assertions across resources.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Disable heavy campaign relationship seeding to keep the shared smoke tests lightweight.
        config(['factory.seed_campaign_relations' => false]);

        // Seed a privileged administrator so Filament pages authenticate successfully in tests.
        $this->adminUser = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);
        $this->actingAs($this->adminUser);

        // Provide missing schema tab classes for list pages that forgot to import Filament's schema tab component.
        if (! class_exists('App\\Filament\\Resources\\CampaignResource\\Pages\\SchemaTab')) {
            class_alias(SchemaTabComponent::class, 'App\\Filament\\Resources\\CampaignResource\\Pages\\SchemaTab');
        }

        // Ensure slider resources can resolve their expected column without altering production migrations.
        if (! Schema::hasColumn('sliders', 'name')) {
            Schema::table('sliders', static function (Blueprint $table): void {
                $table->string('name')->nullable()->after('title');
            });
        }

        // Alias the historical VariantStock model to the consolidated VariantInventory implementation used by the resource.
        if (! class_exists('App\\Models\\VariantStock')) {
            class_alias(VariantInventory::class, 'App\\Models\\VariantStock');
        }
    }

    /**
     * Provide resource and page combinations that previously lacked dedicated coverage.
     *
     * @return array<string, array{resource: class-string, page: class-string, factory: callable(): array}>
     */
    public static function resourceDataProvider(): array
    {
        return [
            'audit_trails' => [
                'resource' => AuditTrailResource::class,
                'page'     => \App\Filament\Resources\AuditTrailResource\Pages\ListAuditTrails::class,
                'factory'  => static function (): array {
                    // Capture a user record to associate with the audit entry for realistic payloads.
                    $auditable = User::factory()->create();

                    return [
                        AuditTrail::query()->create([
                            'auditable_type' => $auditable->getMorphClass(),
                            'auditable_id'   => $auditable->getKey(),
                            'event'          => 'updated',
                            'diff'           => [
                                'name' => [
                                    'previous' => 'Before',
                                    'current'  => 'After',
                                ],
                            ],
                        ]),
                    ];
                },
            ],
            'brands' => [
                'resource' => BrandResource::class,
                'page'     => \App\Filament\Resources\BrandResource\Pages\ListBrands::class,
                'factory'  => static fn (): array => [Brand::factory()->create()],
            ],
            'campaigns' => [
                'resource' => CampaignResource::class,
                'page'     => \App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class,
                'factory'  => static fn (): array => [Campaign::factory()->create(['status' => 'active'])],
            ],
            'cities' => [
                'resource' => CityResource::class,
                'page'     => \App\Filament\Resources\CityResource\Pages\ListCities::class,
                'factory'  => static fn (): array => [City::factory()->create()],
            ],
            'collections' => [
                'resource' => CollectionResource::class,
                'page'     => \App\Filament\Resources\CollectionResource\Pages\ListCollections::class,
                'factory'  => static fn (): array => [Collection::factory()->create()],
            ],
            'collection_rules' => [
                'resource' => CollectionRuleResource::class,
                'page'     => \App\Filament\Resources\CollectionRuleResource\Pages\ListCollectionRules::class,
                'factory'  => static fn (): array => [CollectionRule::factory()->create()],
            ],
            'enum_management' => [
                'resource' => EnumManagementResource::class,
                'page'     => \App\Filament\Resources\EnumManagementResource\Pages\ListEnumManagement::class,
                'factory'  => static fn (): array => [EnumValue::factory()->create()],
            ],
            'posts' => [
                'resource' => PostResource::class,
                'page'     => \App\Filament\Resources\PostResource\Pages\ListPosts::class,
                'factory'  => static fn (): array => [Post::factory()->published()->create()],
            ],
            'product_variants' => [
                'resource' => ProductVariantResource::class,
                'page'     => \App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants::class,
                'factory'  => static fn (): array => [ProductVariant::factory()->create()],
            ],
            'recommendation_analytics' => [
                'resource' => RecommendationAnalyticsResource::class,
                'page'     => \App\Filament\Resources\RecommendationAnalyticsResource\Pages\ListRecommendationAnalytics::class,
                'factory'  => static function (): array {
                    $block = RecommendationBlock::query()->create([
                        'name'             => 'coverage-block',
                        'title'            => 'Coverage Block',
                        'description'      => 'Ensures analytics tables hydrate inside tests.',
                        'config_ids'       => [],
                        'is_active'        => true,
                        'max_products'     => 4,
                        'cache_duration'   => 3600,
                        'display_settings' => ['layout' => 'grid', 'columns' => 3],
                    ]);

                    return [RecommendationAnalytics::factory()->for($block, 'block')->create()];
                },
            ],
            'referral_campaigns' => [
                'resource' => ReferralCampaignResource::class,
                'page'     => \App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns::class,
                'factory'  => static fn (): array => [ReferralCampaign::factory()->create()],
            ],
            'settings' => [
                'resource' => SettingResource::class,
                'page'     => \App\Filament\Resources\Settings\Pages\ListSettings::class,
                'factory'  => static fn (): array => [Setting::factory()->create()],
            ],
            'slider_translations' => [
                'resource' => SliderTranslationResource::class,
                'page'     => \App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations::class,
                'factory'  => static function (): array {
                    $slider = Slider::query()->create([
                        'name'             => 'Coverage Slider',
                        'title'            => 'Coverage Slide',
                        'description'      => 'Ensures slider translations mount during smoke tests.',
                        'background_color' => '#ffffff',
                        'text_color'       => '#000000',
                        'sort_order'       => 1,
                        'is_active'        => true,
                    ]);

                    return [SliderTranslation::factory()->english()->for($slider, 'slider')->create()];
                },
            ],
            'system_settings' => [
                'resource' => SystemSettingResource::class,
                'page'     => \App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings::class,
                'factory'  => static fn (): array => [SystemSetting::factory()->create()],
            ],
            'user_management' => [
                'resource' => UserManagementResource::class,
                'page'     => \App\Filament\Resources\UserManagementResource\Pages\ListUsers::class,
                'factory'  => static fn (): array => [User::factory()->create()],
            ],
            'user_preferences' => [
                'resource' => UserPreferenceResource::class,
                'page'     => \App\Filament\Resources\UserPreferenceResource\Pages\ListUserPreferences::class,
                'factory'  => static fn (): array => [UserPreference::factory()->create()],
            ],
            'variant_inventory' => [
                'resource' => VariantStockResource::class,
                'page'     => \App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks::class,
                'factory'  => static fn (): array => [VariantInventory::factory()->create()],
            ],
        ];
    }

    /**
     * @dataProvider resourceDataProvider
     */
    public function test_missing_resource_lists_render_records(string $resourceClass, string $pageClass, callable $factory): void
    {
        // Confirm the supplied class really is a Filament resource so accidental typos fail fast.
        self::assertTrue(is_subclass_of($resourceClass, Resource::class));

        // Generate realistic records using the provided factory callback.
        $records = $factory();

        // Mount the Filament list page to ensure the table hydrates without throwing errors.
        Livewire::test($pageClass)
            ->call('loadTable') // Explicitly hydrate the deferred table dataset introduced in Filament v4.
            ->assertCanSeeTableRecords($records);
    }
}
