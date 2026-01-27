<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(Dashboard::class)]
final class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_dashboard_configuration_exposes_expected_widgets(): void
    {
        $page = app(Dashboard::class);

        $expectedWidgets = [
            \App\Filament\Widgets\DashboardKpiWidget::class,
            \App\Filament\Widgets\DashboardTimeSeriesWidget::class,
            \App\Filament\Widgets\DashboardRecentOrdersTable::class,
            \App\Filament\Widgets\DashboardLowStockTable::class,
            \App\Filament\Widgets\DashboardQuickActionsWidget::class,
        ];

        $this->assertSame($expectedWidgets, $page->getWidgets());
    }

    public function test_dashboard_has_correct_column_configuration(): void
    {
        $page = app(Dashboard::class);

        $expectedColumns = [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
        ];

        $this->assertSame($expectedColumns, $page->getColumns());
    }

    public function test_dashboard_navigation_metadata(): void
    {
        $this->assertSame('heroicon-o-home', Dashboard::getNavigationIcon());
        $this->assertSame('dashboard', Dashboard::getRoutePath());
        $this->assertFalse(Dashboard::shouldRegisterNavigation());
        $this->assertSame(1, Dashboard::getNavigationSort());
    }

    public function test_dashboard_access_control_with_no_permissions(): void
    {
        config(['dashboard.permissions' => []]);

        $this->assertTrue(Dashboard::canAccess());
    }

    public function test_dashboard_access_control_with_valid_permissions(): void
    {
        config(['dashboard.permissions' => ['dashboard.view']]);
        Gate::define('dashboard.view', fn () => true);

        $this->assertTrue(Dashboard::canAccess());
    }

    public function test_dashboard_access_denied_without_permissions(): void
    {
        config(['dashboard.permissions' => ['dashboard.view']]);
        Gate::define('dashboard.view', fn () => false);

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $this->assertFalse(Dashboard::canAccess());
    }

    public function test_dashboard_access_granted_for_admin_users(): void
    {
        config(['dashboard.permissions' => ['dashboard.view']]);
        Gate::define('dashboard.view', fn () => false);

        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $this->assertTrue(Dashboard::canAccess());
    }

    public function test_dashboard_renders_successfully(): void
    {
        Livewire::test(Dashboard::class)
            ->assertSuccessful();
    }

    public function test_dashboard_title_and_heading(): void
    {
        $page = app(Dashboard::class);

        $this->assertSame(trans('messages.admin'), $page->getTitle());
        $this->assertSame('Dashboard', $page->getHeading());
    }

    public function test_dashboard_navigation_label(): void
    {
        $this->assertSame(trans('messages.admin'), Dashboard::getNavigationLabel());
    }
}
