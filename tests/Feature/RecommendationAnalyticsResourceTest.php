<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RecommendationAnalyticsResource\Pages\CreateRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalyticsResource\Pages\ListRecommendationAnalytics;
use App\Models\Product;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class RecommendationAnalyticsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Prepare the Filament admin panel so Livewire resources load identical configuration as production.
        $this->resolveAdminPanel();

        // Seed the permissions table to ensure the elevated role can access analytics tooling.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create a reusable administrator with unrestricted permissions for analytics operations.
        $this->adminUser = User::factory()->create([
            'email'    => 'analytics-admin@example.test',
            'is_admin' => true,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_displays_recent_recommendation_events(): void
    {
        // Persist analytics events across multiple actions so the listing showcases diverse payloads.
        $viewEvent = RecommendationAnalytics::factory()->create(['action' => 'view']);
        $clickEvent = RecommendationAnalytics::factory()->create(['action' => 'click']);

        Livewire::actingAs($this->adminUser)
            ->test(ListRecommendationAnalytics::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$viewEvent, $clickEvent]);
    }

    public function test_action_filter_isolates_specific_interactions(): void
    {
        // Generate two analytics rows to confirm the action filter narrows results correctly.
        $viewEvent = RecommendationAnalytics::factory()->create(['action' => 'view']);
        $purchaseEvent = RecommendationAnalytics::factory()->create(['action' => 'purchase']);

        Livewire::actingAs($this->adminUser)
            ->test(ListRecommendationAnalytics::class)
            ->call('loadTable')
            ->filterTable('action', 'purchase')
            ->assertCanSeeTableRecords([$purchaseEvent])
            ->assertCanNotSeeTableRecords([$viewEvent]);
    }

    public function test_admin_can_create_recommendation_analytics_entry(): void
    {
        $block = RecommendationBlock::factory()->create();
        $config = RecommendationConfig::factory()->create();
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(CreateRecommendationAnalytics::class)
            ->fillForm([
                'block_id'        => $block->getKey(),
                'config_id'       => $config->getKey(),
                'user_id'         => $customer->getKey(),
                'product_id'      => $product->getKey(),
                'action'          => 'click',
                'date'            => now()->toDateString(),
                'ctr'             => '0.1234',
                'conversion_rate' => '0.2500',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_analytics', [
            'block_id'  => $block->getKey(),
            'config_id' => $config->getKey(),
            'user_id'   => $customer->getKey(),
            'product_id'=> $product->getKey(),
            'action'    => 'click',
        ]);
    }
}
