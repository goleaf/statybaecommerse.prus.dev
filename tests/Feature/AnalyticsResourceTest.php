<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\AnalyticsResource;
use App\Filament\Resources\AnalyticsResource\Pages\AnalyticsDashboard;
use App\Models\Channel;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Feature coverage for the Filament analytics dashboard resource.
 */
final class AnalyticsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Channel $defaultChannel;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament's admin panel is registered before interacting with Livewire components.
        $this->resolveAdminPanel();

        // Provision a deterministic administrator so policy checks pass consistently.
        $this->adminUser = User::factory()->admin()->create([
            'email' => 'analytics-admin@example.test',
        ]);

        // Seed a reusable primary sales channel for analytics test scenarios.
        $this->defaultChannel = Channel::factory()->create([
            'name' => 'Main Web Channel',
        ]);

        $this->actingAs($this->adminUser);
    }

    /**
     * Create an order with optional overrides so tests can focus on assertion logic.
     */
    private function createOrder(array $overrides = []): Order
    {
        // Default to the seeded admin user and channel unless a test overrides them.
        $user = $overrides['user'] ?? $this->adminUser;
        $channel = $overrides['channel'] ?? $this->defaultChannel;
        $itemCount = (int) ($overrides['items'] ?? 2);

        // Strip helper-only keys before mass assigning the order attributes.
        unset($overrides['user'], $overrides['channel'], $overrides['items']);

        // Merge deterministic defaults so analytics assertions remain stable across runs.
        $attributes = array_merge([
            'status' => 'pending',
            'total' => 750.00,
            'created_at' => now(),
        ], $overrides);

        $order = Order::factory()
            ->for($user)
            ->for($channel)
            ->create($attributes);

        // Populate the order with a configurable number of items for the items_count column.
        OrderItem::factory()
            ->forOrder($order)
            ->count(max(1, $itemCount))
            ->create();

        return $order->fresh(['user', 'channel', 'items']);
    }

    /**
     * Pending orders should surface in the navigation badge to highlight outstanding fulfilment work.
     */
    public function test_navigation_badge_counts_only_pending_orders(): void
    {
        $this->createOrder(['status' => 'pending']);
        $this->createOrder(['status' => 'pending']);
        $this->createOrder(['status' => 'completed']);

        self::assertSame('2', AnalyticsResource::getNavigationBadge());
    }

    /**
     * When no pending orders exist the badge should disappear to avoid distracting noise.
     */
    public function test_navigation_badge_returns_null_when_no_pending_orders_exist(): void
    {
        $this->createOrder(['status' => 'completed']);
        $this->createOrder(['status' => 'delivered']);

        self::assertNull(AnalyticsResource::getNavigationBadge());
    }

    /**
     * The listing should render the configured columns and expose seeded records.
     */
    public function test_dashboard_table_displays_core_columns_and_records(): void
    {
        $order = $this->createOrder([
            'status' => 'pending',
            'total' => 915.50,
            'items' => 3,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(AnalyticsDashboard::class)
            ->call('loadTable')
            ->assertTableColumnExists('number')
            ->assertTableColumnExists('user.name')
            ->assertTableColumnExists('user.email')
            ->assertTableColumnExists('channel.name')
            ->assertTableColumnExists('items_count')
            ->assertTableColumnExists('total')
            ->assertTableColumnExists('status')
            ->assertCanSeeTableRecords([$order]);
    }

    /**
     * Status filters must scope the dataset to only the matching lifecycle state.
     */
    public function test_status_filter_limits_results_to_selected_state(): void
    {
        $pendingOrder = $this->createOrder(['status' => 'pending']);
        $completedOrder = $this->createOrder(['status' => 'completed']);

        Livewire::actingAs($this->adminUser)
            ->test(AnalyticsDashboard::class)
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords([$pendingOrder])
            ->assertCanNotSeeTableRecords([$completedOrder]);
    }

    /**
     * The user select filter should narrow results to orders belonging to the chosen customer.
     */
    public function test_user_filter_scopes_orders_to_selected_customer(): void
    {
        $otherUser = User::factory()->create([
            'email' => 'secondary@example.test',
        ]);

        $visibleOrder = $this->createOrder(['user' => $this->adminUser]);
        $hiddenOrder = $this->createOrder(['user' => $otherUser]);

        Livewire::actingAs($this->adminUser)
            ->test(AnalyticsDashboard::class)
            ->filterTable('user_id', $this->adminUser->id)
            ->assertCanSeeTableRecords([$visibleOrder])
            ->assertCanNotSeeTableRecords([$hiddenOrder]);
    }

    /**
     * Analysts should be able to focus on a single sales channel when reviewing metrics.
     */
    public function test_channel_filter_scopes_orders_to_selected_channel(): void
    {
        $secondaryChannel = Channel::factory()->create([
            'name' => 'Wholesale Channel',
        ]);

        $visibleOrder = $this->createOrder(['channel' => $this->defaultChannel]);
        $hiddenOrder = $this->createOrder(['channel' => $secondaryChannel]);

        Livewire::actingAs($this->adminUser)
            ->test(AnalyticsDashboard::class)
            ->filterTable('channel_id', $this->defaultChannel->id)
            ->assertCanSeeTableRecords([$visibleOrder])
            ->assertCanNotSeeTableRecords([$hiddenOrder]);
    }

    /**
     * The high value shortcut should exclude smaller baskets below the configured €500 threshold.
     */
    public function test_high_value_filter_excludes_low_total_orders(): void
    {
        $highValueOrder = $this->createOrder(['total' => 1200.00]);
        $regularOrder = $this->createOrder(['total' => 320.00]);

        Livewire::actingAs($this->adminUser)
            ->test(AnalyticsDashboard::class)
            ->filterTable('high_value', true)
            ->assertCanSeeTableRecords([$highValueOrder])
            ->assertCanNotSeeTableRecords([$regularOrder]);
    }

    /**
     * Date range filtering should trim orders outside the supplied window.
     */
    public function test_date_range_filter_limits_orders_to_requested_window(): void
    {
        $now = Carbon::now()->startOfDay();

        $recentOrder = $this->createOrder([
            'created_at' => $now->copy()->addHours(10),
        ]);

        $staleOrder = $this->createOrder([
            'created_at' => $now->copy()->subWeeks(2),
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(AnalyticsDashboard::class)
            ->filterTable('created_at', [
                'range' => [
                    'start' => $now->copy()->subDay()->format('Y-m-d'),
                    'end' => $now->copy()->addDay()->format('Y-m-d'),
                ],
            ])
            ->assertCanSeeTableRecords([$recentOrder])
            ->assertCanNotSeeTableRecords([$staleOrder]);
    }

    /**
     * The quick filter for "this month" should include only orders created within the current calendar month.
     */
    public function test_this_month_filter_only_exposes_current_period_orders(): void
    {
        $now = Carbon::now();

        $thisMonthOrder = $this->createOrder([
            'created_at' => $now->copy()->startOfMonth()->addDays(2),
        ]);

        $previousMonthOrder = $this->createOrder([
            'created_at' => $now->copy()->subMonth()->startOfMonth()->addDays(3),
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(AnalyticsDashboard::class)
            ->filterTable('this_month', true)
            ->assertCanSeeTableRecords([$thisMonthOrder])
            ->assertCanNotSeeTableRecords([$previousMonthOrder]);
    }

    /**
     * Every row should surface the view action so analysts can deep-link into the order record.
     */
    public function test_view_action_is_available_for_order_records(): void
    {
        $order = $this->createOrder();

        Livewire::actingAs($this->adminUser)
            ->test(AnalyticsDashboard::class)
            ->assertTableActionVisible('view', $order);
    }

    /**
     * Header actions should be callable without triggering validation or runtime errors.
     */
    public function test_header_actions_are_invokable_without_errors(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(AnalyticsDashboard::class)
            ->callAction('refresh_data')
            ->callAction('export_report')
            ->assertHasNoActionErrors();
    }
}
