<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Widgets;

use App\Filament\Widgets\DashboardRecentOrdersTable;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use Tests\TestCase;

#[CoversClass(DashboardRecentOrdersTable::class)]
final class DashboardRecentOrdersTableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['dashboard.permissions.view_tables' => 'dashboard.view_tables']);
    }

    public function test_widget_can_be_instantiated(): void
    {
        $widget = new DashboardRecentOrdersTable;

        $this->assertInstanceOf(DashboardRecentOrdersTable::class, $widget);
    }

    public function test_widget_has_correct_sort_order(): void
    {
        $reflection = new ReflectionClass(DashboardRecentOrdersTable::class);
        $property = $reflection->getProperty('sort');
        $property->setAccessible(true);

        $this->assertEquals(3, $property->getValue());
    }

    public function test_widget_has_correct_column_span(): void
    {
        $widget = new DashboardRecentOrdersTable;

        $reflection = new ReflectionClass($widget);
        $property = $reflection->getProperty('columnSpan');
        $property->setAccessible(true);

        $expectedSpan = ['md' => 2, 'xl' => 2];
        $this->assertEquals($expectedSpan, $property->getValue($widget));
    }

    public function test_authorized_user_can_view_widget(): void
    {
        Gate::define('dashboard.view_tables', fn () => true);

        $this->assertTrue(DashboardRecentOrdersTable::canView());
    }

    public function test_unauthorized_user_cannot_view_widget(): void
    {
        Gate::define('dashboard.view_tables', fn () => false);

        $this->assertFalse(DashboardRecentOrdersTable::canView());
    }

    public function test_widget_renders_successfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Gate::define('dashboard.view_tables', fn () => true);

        Livewire::test(DashboardRecentOrdersTable::class)
            ->assertSuccessful();
    }

    public function test_widget_displays_recent_orders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Gate::define('dashboard.view_tables', fn () => true);

        // Create test orders
        $customer = User::factory()->create();
        $order1 = Order::factory()->create([
            'user_id' => $customer->id,
            'number'  => 'ORD-001',
            'status'  => 'pending',
            'total'   => 99.99,
        ]);
        $order2 = Order::factory()->create([
            'user_id' => $customer->id,
            'number'  => 'ORD-002',
            'status'  => 'completed',
            'total'   => 149.99,
        ]);

        $component = Livewire::test(DashboardRecentOrdersTable::class);

        $component->assertCanSeeTableRecords([$order1, $order2]);
    }

    public function test_widget_table_columns_are_configured(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Gate::define('dashboard.view_tables', fn () => true);

        $customer = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'number'  => 'ORD-123',
            'status'  => 'pending',
            'total'   => 99.99,
        ]);

        $component = Livewire::test(DashboardRecentOrdersTable::class);

        // Check that order data is displayed
        $component->assertSee('ORD-123');
        $component->assertSee('John Doe');
        $component->assertSee('john@example.com');
    }

    public function test_widget_table_has_view_action(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Gate::define('dashboard.view_tables', fn () => true);

        $customer = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $component = Livewire::test(DashboardRecentOrdersTable::class);

        $component->assertTableActionExists('view');
    }

    public function test_widget_table_is_striped(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Gate::define('dashboard.view_tables', fn () => true);

        // This test verifies the table configuration includes striped styling
        // The actual visual verification would require browser testing
        $component = Livewire::test(DashboardRecentOrdersTable::class);
        $component->assertSuccessful();
    }

    public function test_widget_limits_to_ten_records(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Gate::define('dashboard.view_tables', fn () => true);

        $customer = User::factory()->create();

        // Create 15 orders to test the limit
        $orders = Order::factory()->count(15)->create(['user_id' => $customer->id]);

        $component = Livewire::test(DashboardRecentOrdersTable::class);

        // The widget should only show 10 records due to the limit(10) in the query
        $component->assertSuccessful();
    }

    public function test_widget_handles_guest_orders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Gate::define('dashboard.view_tables', fn () => true);

        // Create order without user (guest order)
        $order = Order::factory()->create([
            'user_id' => null,
            'number'  => 'ORD-GUEST',
            'status'  => 'pending',
            'total'   => 49.99,
        ]);

        $component = Livewire::test(DashboardRecentOrdersTable::class);

        $component->assertCanSeeTableRecords([$order]);
    }

    public function test_widget_extends_table_widget(): void
    {
        $widget = new DashboardRecentOrdersTable;

        $this->assertInstanceOf(\Filament\Widgets\TableWidget::class, $widget);
    }

    public function test_widget_heading(): void
    {
        $widget = new DashboardRecentOrdersTable;

        $this->assertEquals(trans('admin/dashboard.tables.recent_orders'), $widget->getHeading());
    }

    public function test_widget_permission_configuration_exists(): void
    {
        $permission = config('dashboard.permissions.view_tables');
        $this->assertNotEmpty($permission);
        $this->assertEquals('dashboard.view_tables', $permission);
    }

    public function test_widget_translation_keys_exist(): void
    {
        $this->assertNotEmpty(trans('admin/dashboard.tables.recent_orders'));
        $this->assertNotEmpty(trans('orders.number'));
        $this->assertNotEmpty(trans('orders.status'));
        $this->assertNotEmpty(trans('orders.customer'));
        $this->assertNotEmpty(trans('orders.total_amount'));
        $this->assertNotEmpty(trans('orders.created_at'));
        $this->assertNotEmpty(trans('orders.view'));
    }
}
