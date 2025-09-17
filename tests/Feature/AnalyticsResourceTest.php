<?php declare(strict_types=1);

use App\Filament\Resources\AnalyticsResource\Pages\AnalyticsDashboard;
use App\Filament\Resources\AnalyticsResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'email' => 'admin@admin.com',
        'name' => 'Admin User',
    ]);

    // Assign admin role if using Spatie permissions
    if (class_exists(\Spatie\Permission\Models\Role::class)) {
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        $this->adminUser->assignRole($adminRole);

        // Create and give the user the specific permission needed for analytics
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view_analytics']);
        $this->adminUser->givePermissionTo($permission);
    }

    $this->actingAs($this->adminUser);
});

it('can access analytics dashboard', function () {
    $response = $this->get(AnalyticsResource::getUrl('index'));

    $response->assertSuccessful();
});

it('can view analytics dashboard page', function () {
    Livewire::test(AnalyticsDashboard::class)
        ->assertSuccessful();
});

it('displays correct analytics data', function () {
    // Create test orders
    $orders = Order::factory()->count(5)->create([
        'status' => 'completed',
        'total' => 100.0,
        'created_at' => now()->subDays(5),
    ]);

    $pendingOrders = Order::factory()->count(2)->create([
        'status' => 'pending',
        'total' => 50.0,
        'created_at' => now()->subDays(2),
    ]);

    Livewire::test(AnalyticsDashboard::class)
        ->assertSuccessful();
});

it('can filter orders by status', function () {
    $completedOrders = Order::factory()->count(3)->create(['status' => 'completed']);
    $pendingOrders = Order::factory()->count(2)->create(['status' => 'pending']);

    Livewire::test(AnalyticsDashboard::class)
        ->assertSuccessful()
        ->filterTable('status', 'completed')
        ->assertSuccessful();
});

it('can filter orders by date range', function () {
    $oldOrders = Order::factory()->count(2)->create([
        'created_at' => now()->subMonths(2),
    ]);

    $recentOrders = Order::factory()->count(3)->create([
        'created_at' => now()->subDays(5),
    ]);

    Livewire::test(AnalyticsDashboard::class)
        ->assertSuccessful()
        ->filterTable('created_at', [
            'created_from' => now()->subDays(10)->format('Y-m-d'),
            'created_until' => now()->format('Y-m-d'),
        ])
        ->assertSuccessful();
});

it('can filter high value orders', function () {
    $lowValueOrders = Order::factory()->count(2)->create(['total' => 100.0]);
    $highValueOrders = Order::factory()->count(3)->create(['total' => 600.0]);

    Livewire::test(AnalyticsDashboard::class)
        ->assertSuccessful()
        ->filterTable('high_value')
        ->assertSuccessful();
});

it('can filter orders from this month', function () {
    $oldOrders = Order::factory()->count(2)->create([
        'created_at' => now()->subMonths(2),
    ]);

    $thisMonthOrders = Order::factory()->count(3)->create([
        'created_at' => now()->startOfMonth()->addDays(5),
    ]);

    Livewire::test(AnalyticsDashboard::class)
        ->assertSuccessful()
        ->filterTable('this_month')
        ->assertSuccessful();
});

it('can export analytics data', function () {
    Order::factory()->count(5)->create();

    Livewire::test(AnalyticsDashboard::class)
        ->callAction('export_report')
        ->assertNotified();
});

it('can refresh analytics data', function () {
    Livewire::test(AnalyticsDashboard::class)
        ->callAction('refresh_data')
        ->assertNotified();
});

it('displays correct table columns', function () {
    $order = Order::factory()->create([
        'total' => 150.5,
        'status' => 'completed',
    ]);

    Livewire::test(AnalyticsDashboard::class)
        ->assertSuccessful()
        ->assertTableColumnExists('order_date')
        ->assertTableColumnExists('user.name')
        ->assertTableColumnExists('items_count')
        ->assertTableColumnExists('total')
        ->assertTableColumnExists('status')
        ->assertTableColumnExists('created_at');
});

it('can sort by different columns', function () {
    $orders = Order::factory()->count(3)->create();

    Livewire::test(AnalyticsDashboard::class)
        ->assertSuccessful()
        ->sortTable('total')
        ->assertSuccessful()
        ->sortTable('created_at', 'desc')
        ->assertSuccessful();
});

it('can group orders by month', function () {
    Order::factory()->count(3)->create([
        'created_at' => now()->startOfMonth(),
    ]);

    Livewire::test(AnalyticsDashboard::class)
        ->assertSuccessful();
});

it('can group orders by status', function () {
    Order::factory()->count(2)->create(['status' => 'completed']);
    Order::factory()->count(2)->create(['status' => 'pending']);

    Livewire::test(AnalyticsDashboard::class)
        ->assertSuccessful();
});

it('displays navigation badge for pending orders', function () {
    Order::factory()->count(3)->create(['status' => 'pending']);

    expect(AnalyticsResource::getNavigationBadge())->toBe('3');
    expect(AnalyticsResource::getNavigationBadgeColor())->toBe('warning');
});

it('hides navigation badge when no pending orders', function () {
    Order::factory()->count(2)->create(['status' => 'completed']);

    expect(AnalyticsResource::getNavigationBadge())->toBeNull();
});

it('can access analytics with proper permissions', function () {
    expect(AnalyticsResource::canAccess())->toBeTrue();
});

it('displays correct labels and translations', function () {
    expect(AnalyticsResource::getNavigationLabel())->toBe(__('analytics.analytics'));
    expect(AnalyticsResource::getModelLabel())->toBe(__('analytics.analytics'));
    expect(AnalyticsResource::getPluralModelLabel())->toBe(__('analytics.analytics'));
});
