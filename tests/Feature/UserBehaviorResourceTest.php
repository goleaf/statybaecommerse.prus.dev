<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserBehaviorResource\Pages\ListUserBehaviors;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserBehavior;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression coverage for the Filament user behaviour resource.
 */
final class UserBehaviorResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Product $product;

    private Category $category;

    public static function setUpBeforeClass(): void
    {
        // Force the lightweight SQLite schema so the behaviour factories stay deterministic in CI.
        putenv('TEST_FORCE_MINIMAL_SQLITE=1');
        $_ENV['TEST_FORCE_MINIMAL_SQLITE'] = '1';
        $_SERVER['TEST_FORCE_MINIMAL_SQLITE'] = '1';

        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Authenticate a deterministic admin and supporting catalogue fixtures for every scenario.
        $this->adminUser = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->product = Product::factory()->create();
        $this->category = Category::factory()->create();

        $this->actingAs($this->adminUser);
    }

    /**
     * Provide a central helper so every test exercises the same baseline payload.
     */
    private function createBehavior(array $overrides = []): UserBehavior
    {
        // Merge overrides so callers can provide partial payloads without rebuilding the entire factory stub.
        return UserBehavior::factory()->create(array_merge([
            'user_id'       => $this->adminUser->id,
            'product_id'    => $this->product->id,
            'category_id'   => $this->category->id,
            'behavior_type' => 'view',
        ], $overrides));
    }

    public function test_list_page_displays_expected_columns(): void
    {
        // Arrange: ensure at least one record exists so column assertions hit rendered output.
        $behavior = $this->createBehavior();

        // Act & Assert: the listing should expose all toggleable columns configured on the resource.
        Livewire::actingAs($this->adminUser)
            ->test(ListUserBehaviors::class)
            ->loadTable()
            ->assertTableColumnExists('user.name')
            ->assertTableColumnExists('behavior_type')
            ->assertTableColumnExists('product.name')
            ->assertTableColumnExists('category.name')
            ->assertTableColumnExists('session_id')
            ->assertTableColumnExists('created_at')
            ->assertCanSeeTableRecords([$behavior]);
    }

    public function test_behavior_type_multi_select_filter_limits_visible_records(): void
    {
        // Arrange: seed one record for each behaviour so the multi-select filter has variety to trim.
        $viewBehavior = $this->createBehavior(['behavior_type' => 'view']);
        $clickBehavior = $this->createBehavior(['behavior_type' => 'click']);

        // Act & Assert: choosing the view type should hide the click variant.
        Livewire::actingAs($this->adminUser)
            ->test(ListUserBehaviors::class)
            ->filterTable('behavior_type', ['view'])
            ->assertCanSeeTableRecords([$viewBehavior])
            ->assertCanNotSeeTableRecords([$clickBehavior]);
    }

    public function test_user_filter_scopes_results_to_selected_admin(): void
    {
        // Arrange: attach a secondary user to confirm the select filter hides unrelated activity.
        $otherUser = User::factory()->create();
        $visibleBehavior = $this->createBehavior();
        $hiddenBehavior = $this->createBehavior([
            'user_id'       => $otherUser->id,
            'behavior_type' => 'search',
        ]);

        // Act & Assert: the filter must only surface the authenticated admin's records.
        Livewire::actingAs($this->adminUser)
            ->test(ListUserBehaviors::class)
            ->filterTable('user_id', $this->adminUser->id)
            ->assertCanSeeTableRecords([$visibleBehavior])
            ->assertCanNotSeeTableRecords([$hiddenBehavior]);
    }

    public function test_created_at_range_filter_trims_outdated_behaviours(): void
    {
        // Arrange: generate one fresh and one stale behaviour to exercise the date range helper.
        $recentBehavior = $this->createBehavior(['created_at' => now()]);
        $staleBehavior = $this->createBehavior([
            'behavior_type' => 'purchase',
            'created_at'    => now()->subWeeks(2),
        ]);

        // Act & Assert: providing a range covering today should exclude the historical record.
        Livewire::actingAs($this->adminUser)
            ->test(ListUserBehaviors::class)
            ->filterTable('created_at', [
                'range' => [
                    'start' => now()->subDay()->format('Y-m-d'),
                    'end'   => now()->format('Y-m-d'),
                ],
            ])
            ->assertCanSeeTableRecords([$recentBehavior])
            ->assertCanNotSeeTableRecords([$staleBehavior]);
    }

    public function test_ternary_product_filter_hides_records_without_relationships(): void
    {
        // Arrange: create a behaviour lacking the product relation to validate ternary querying.
        $withProduct = $this->createBehavior(['behavior_type' => 'view']);
        $withoutProduct = $this->createBehavior([
            'product_id'    => null,
            'behavior_type' => 'click',
        ]);

        // Act & Assert: enabling the "has product" filter should remove product-less entries.
        Livewire::actingAs($this->adminUser)
            ->test(ListUserBehaviors::class)
            ->filterTable('has_product', 'true')
            ->assertCanSeeTableRecords([$withProduct])
            ->assertCanNotSeeTableRecords([$withoutProduct]);
    }

    public function test_table_actions_dispatch_expected_notifications(): void
    {
        // Arrange: fake notifications so we can assert the exact flash messages configured on actions.
        FilamentNotification::fake();
        $behavior = $this->createBehavior();

        $component = Livewire::actingAs($this->adminUser)
            ->test(ListUserBehaviors::class)
            ->loadTable();

        // Act & Assert: each single-record action should emit its success notification.
        $component
            ->callTableAction('analyze', $behavior)
            ->assertNotified(__('admin.user_behaviors.analysis_completed'))
            ->callTableAction('view_user_journey', $behavior)
            ->assertNotified(__('admin.user_behaviors.user_journey_analyzed'))
            ->callTableAction('view_session_details', $behavior)
            ->assertNotified(__('admin.user_behaviors.session_analyzed'));
    }

    public function test_bulk_actions_emit_success_messages(): void
    {
        // Arrange: capture notifications and a batch of behaviours for the export/inference flows.
        FilamentNotification::fake();
        $behaviors = UserBehavior::factory()->count(2)->create([
            'user_id'     => $this->adminUser->id,
            'product_id'  => $this->product->id,
            'category_id' => $this->category->id,
        ]);

        // Act & Assert: calling each bulk action should surface the translated success banner.
        $component = Livewire::actingAs($this->adminUser)
            ->test(ListUserBehaviors::class)
            ->loadTable();

        $component
            ->callTableBulkAction('export_analytics', $behaviors)
            ->assertNotified(__('admin.user_behaviors.exported_successfully'))
            ->callTableBulkAction('analyze_selected', $behaviors)
            ->assertNotified(__('admin.user_behaviors.bulk_analysis_completed', ['count' => $behaviors->count()]))
            ->callTableBulkAction('generate_insights', $behaviors)
            ->assertNotified(__('admin.user_behaviors.insights_generated', ['count' => $behaviors->count()]));
    }
}
