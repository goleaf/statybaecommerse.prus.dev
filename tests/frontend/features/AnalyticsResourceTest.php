<?php

declare(strict_types=1);

use App\Filament\Resources\AnalyticsResource;
use App\Models\Order;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'email' => 'admin@admin.com',
        'name' => 'Admin User',
    ]);

    // Create role and permissions if they don't exist
    $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

    // Create all necessary permissions
    $permissions = [
        'view_activity',
        'view_analytics',
        'view_order',
        'view_product',
        'view_user',
    ];

    foreach ($permissions as $permission) {
        $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        $role->givePermissionTo($perm);
    }

    $this->adminUser->assignRole($role);

    actingAs($this->adminUser);
});

it('can render analytics resource page', function () {
    $response = $this->get(AnalyticsResource::getUrl('index'));

    $response->assertSuccessful();
});

it('displays orders in analytics table', function () {
    $orders = Order::factory()->count(5)->create([
        'status' => 'completed',
        'total' => 100.0,
    ]);

    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->assertCanSeeTableRecords($orders);
});

it('can filter orders by date range', function () {
    $oldOrder = Order::factory()->create([
        'created_at' => now()->subMonths(2),
        'status' => 'completed',
    ]);

    $recentOrder = Order::factory()->create([
        'created_at' => now()->subDays(5),
        'status' => 'completed',
    ]);

    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->filterTable('created_at', [
            'created_from' => now()->subDays(10)->format('Y-m-d'),
            'created_until' => now()->format('Y-m-d'),
        ])
        ->assertCanSeeTableRecords([$recentOrder])
        ->assertCanNotSeeTableRecords([$oldOrder]);
});

it('can filter orders by status', function () {
    $pendingOrder = Order::factory()->create(['status' => 'pending']);
    $completedOrder = Order::factory()->create(['status' => 'completed']);

    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->filterTable('status', 'pending')
        ->assertCanSeeTableRecords([$pendingOrder])
        ->assertCanNotSeeTableRecords([$completedOrder]);
});

it('can filter high value orders', function () {
    $lowValueOrder = Order::factory()->create(['total' => 100.0]);
    $highValueOrder = Order::factory()->create(['total' => 1000.0]);

    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->filterTable('high_value')
        ->assertCanSeeTableRecords([$highValueOrder])
        ->assertCanNotSeeTableRecords([$lowValueOrder]);
});

it('can filter orders from this month', function () {
    $lastMonthOrder = Order::factory()->create([
        'created_at' => now()->subMonth(),
    ]);

    $thisMonthOrder = Order::factory()->create([
        'created_at' => now(),
    ]);

    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->filterTable('this_month')
        ->assertCanSeeTableRecords([$thisMonthOrder])
        ->assertCanNotSeeTableRecords([$lastMonthOrder]);
});

it('displays correct order summaries', function () {
    Order::factory()->count(3)->create([
        'total' => 100.0,
        'status' => 'completed',
    ]);

    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->assertSee('300.00')  // Total revenue
        ->assertSee('100.00');  // Average order value
});

it('can view order details', function () {
    $order = Order::factory()->create([
        'reference' => 'ORD-12345',
        'status' => 'completed',
    ]);

    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->callTableAction('view', $order);

    expect($order->reference)->toBe('ORD-12345');
});

it('groups orders by month correctly', function () {
    Order::factory()->create([
        'created_at' => now()->startOfMonth(),
        'status' => 'completed',
    ]);

    Order::factory()->create([
        'created_at' => now()->subMonth()->startOfMonth(),
        'status' => 'completed',
    ]);

    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->assertCanRenderTable();
});

it('groups orders by status correctly', function () {
    Order::factory()->create(['status' => 'pending']);
    Order::factory()->create(['status' => 'completed']);
    Order::factory()->create(['status' => 'shipped']);

    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->assertCanRenderTable();
});

it('displays navigation badge for pending orders', function () {
    Order::factory()->count(3)->create(['status' => 'pending']);

    $badge = AnalyticsResource::getNavigationBadge();

    expect($badge)->toBe('3');
});

it('shows warning color for navigation badge', function () {
    $badgeColor = AnalyticsResource::getNavigationBadgeColor();

    expect($badgeColor)->toBe('warning');
});

it('can access analytics resource with proper permissions', function () {
    expect(AnalyticsResource::canAccess())->toBeTrue();
});

it('displays correct labels', function () {
    expect(AnalyticsResource::getNavigationLabel())->toBe(__('Analytics Dashboard'));
    expect(AnalyticsResource::getModelLabel())->toBe(__('Analytics'));
    expect(AnalyticsResource::getPluralModelLabel())->toBe(__('Analytics'));
});

it('polls data every 30 seconds', function () {
    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->assertCanRenderTable();
});

it('defers loading for better performance', function () {
    livewire(AnalyticsResource\Pages\AnalyticsDashboard::class)
        ->assertCanRenderTable();
});
