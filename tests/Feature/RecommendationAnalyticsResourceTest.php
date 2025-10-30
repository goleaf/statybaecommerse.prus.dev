<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RecommendationAnalyticsResource;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\CreateRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\EditRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\ListRecommendationAnalytics;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use App\Models\User;
use App\Support\Nav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

final class RecommendationAnalyticsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private RecommendationBlock $block;

    private RecommendationConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        // Provision an administrator so Filament policies resolve during Livewire interactions.
        $this->adminUser = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);
        $this->actingAs($this->adminUser);

        // Create shared recommendation entities reused across the tests.
        $this->block = RecommendationBlock::factory()->create([
            'name'  => 'homepage-hero',
            'title' => 'Homepage Hero',
        ]);
        $this->config = RecommendationConfig::factory()->create([
            'name' => 'trending-products',
            'type' => 'trending',
        ]);
    }

    public function test_navigation_metadata_matches_registry(): void
    {
        // Confirm the navigation helpers surface the same metadata exposed through the Nav registry.
        self::assertSame(
            Nav::iconForResource(RecommendationAnalyticsResource::class),
            RecommendationAnalyticsResource::getNavigationIcon(),
        );
        self::assertSame(
            Nav::groupForResource(RecommendationAnalyticsResource::class),
            RecommendationAnalyticsResource::getNavigationGroup(),
        );
        self::assertSame(
            Nav::sortForResource(RecommendationAnalyticsResource::class),
            RecommendationAnalyticsResource::getNavigationSort(),
        );
        self::assertSame(
            __('admin.recommendation_analytics.navigation_label'),
            RecommendationAnalyticsResource::getNavigationLabel(),
        );
    }

    public function test_list_page_displays_existing_records(): void
    {
        // Seed analytics snapshots so the listing can render concrete table rows.
        $firstRecord = RecommendationAnalytics::factory()->create([
            'block_id'  => $this->block->id,
            'config_id' => $this->config->id,
            'user_id'   => User::factory()->create()->id,
            'action'    => 'view',
            'date'      => Carbon::now()->subDay()->toDateString(),
        ]);
        $secondRecord = RecommendationAnalytics::factory()->create([
            'block_id'  => $this->block->id,
            'config_id' => $this->config->id,
            'user_id'   => User::factory()->create()->id,
            'action'    => 'click',
            'date'      => Carbon::now()->toDateString(),
        ]);

        Livewire::test(ListRecommendationAnalytics::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([
                $firstRecord,
                $secondRecord,
            ]);
    }

    public function test_create_form_persists_recommendation_snapshot(): void
    {
        $analyticsUser = User::factory()->create();
        $creationDate = Carbon::now()->toDateString();

        // Drive the create form through Livewire so Filament v4 hydration hooks execute.
        Livewire::test(CreateRecommendationAnalytics::class)
            ->fillForm([
                'block_id'        => $this->block->id,
                'config_id'       => $this->config->id,
                'user_id'         => $analyticsUser->id,
                'product_id'      => null,
                'action'          => 'add_to_cart',
                'date'            => $creationDate,
                'ctr'             => 0.1523,
                'conversion_rate' => 0.0345,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_analytics', [
            'block_id'  => $this->block->id,
            'config_id' => $this->config->id,
            'user_id'   => $analyticsUser->id,
            'action'    => 'add_to_cart',
            'date'      => $creationDate,
        ]);
    }

    public function test_edit_form_updates_metrics(): void
    {
        $record = RecommendationAnalytics::factory()->create([
            'block_id'        => $this->block->id,
            'config_id'       => $this->config->id,
            'user_id'         => User::factory()->create()->id,
            'action'          => 'view',
            'ctr'             => 0.1000,
            'conversion_rate' => 0.0200,
        ]);

        // Adjust the metrics to verify decimal inputs hydrate and persist correctly.
        Livewire::test(EditRecommendationAnalytics::class, [
            'record' => $record->getRouteKey(),
        ])
            ->fillForm([
                'action'          => 'purchase',
                'ctr'             => 0.2500,
                'conversion_rate' => 0.1250,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_analytics', [
            'id'              => $record->id,
            'action'          => 'purchase',
            'ctr'             => '0.2500',
            'conversion_rate' => '0.1250',
        ]);
    }

    public function test_table_filters_scope_by_action_and_date(): void
    {
        $targetDate = Carbon::parse('2025-01-15');

        $matchingRecord = RecommendationAnalytics::factory()->create([
            'block_id'  => $this->block->id,
            'config_id' => $this->config->id,
            'user_id'   => User::factory()->create()->id,
            'action'    => 'click',
            'date'      => $targetDate->toDateString(),
        ]);
        $hiddenRecord = RecommendationAnalytics::factory()->create([
            'block_id'  => $this->block->id,
            'config_id' => $this->config->id,
            'user_id'   => User::factory()->create()->id,
            'action'    => 'view',
            'date'      => $targetDate->clone()->subDay()->toDateString(),
        ]);

        // Apply both filters to ensure the custom Flatpickr field and select filter cooperate.
        Livewire::test(ListRecommendationAnalytics::class)
            ->call('loadTable')
            ->filterTable('action', 'click')
            ->filterTable('date', ['value' => $targetDate->toDateString()])
            ->assertCanSeeTableRecords([$matchingRecord])
            ->assertCanNotSeeTableRecords([$hiddenRecord]);
    }
}
