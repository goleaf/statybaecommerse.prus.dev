<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Filament\Pages\Dashboard;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    public function test_dashboard_can_access_returns_true(): void
    {
        $this->assertTrue(Dashboard::canAccess());
    }

    public function test_dashboard_has_correct_title(): void
    {
        $dashboard = new Dashboard();
        $this->assertEquals(__('admin.navigation.dashboard'), $dashboard->getTitle());
    }

    public function test_dashboard_has_widgets(): void
    {
        $dashboard = new Dashboard();
        $widgets = $dashboard->getWidgets();
        
        $this->assertIsArray($widgets);
        $this->assertNotEmpty($widgets);
    }

    public function test_dashboard_has_columns_configuration(): void
    {
        $dashboard = new Dashboard();
        $columns = $dashboard->getColumns();
        
        $this->assertIsArray($columns);
        $this->assertArrayHasKey('sm', $columns);
        $this->assertArrayHasKey('md', $columns);
        $this->assertArrayHasKey('lg', $columns);
        $this->assertArrayHasKey('xl', $columns);
    }
}
