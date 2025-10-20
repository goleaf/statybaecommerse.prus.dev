<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Feature\TestCase;

final class DashboardAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected string $dashboardRoute = 'filament.admin.pages.dashboard';

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => config('dashboard.permissions.view_kpis')]);
        Permission::firstOrCreate(['name' => config('dashboard.permissions.view_charts')]);
        Permission::firstOrCreate(['name' => config('dashboard.permissions.view_tables')]);
        Permission::firstOrCreate(['name' => config('dashboard.permissions.run_actions')]);
    }

    public function test_authorized_user_sees_all_dashboard_widgets(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(array_values(config('dashboard.permissions')));

        $response = $this->actingAs($user)->get(route($this->dashboardRoute));

        $response->assertOk();
        $response->assertSee(trans('admin/dashboard.kpis.orders_today'));
        $response->assertSee(trans('admin/dashboard.charts.heading'));
        $response->assertSee(trans('admin/dashboard.tables.recent_orders'));
        $response->assertSee(trans('admin/dashboard.actions.heading'));
    }

    public function test_user_without_any_dashboard_permissions_is_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route($this->dashboardRoute));

        $response->assertForbidden();
    }

    public function test_user_with_partial_permission_only_sees_allowed_widgets(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(config('dashboard.permissions.view_tables'));

        $response = $this->actingAs($user)->get(route($this->dashboardRoute));

        $response->assertOk();
        $response->assertSee(trans('admin/dashboard.tables.recent_orders'));
        $response->assertDontSee(trans('admin/dashboard.kpis.orders_today'));
    }
}
