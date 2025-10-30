<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\AdminDashboard;
use App\Filament\Widgets\AdminStatsWidget;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(AdminDashboard::class)]
final class AdminDashboardPageTest extends TestCase
{
    public function test_dashboard_configuration_exposes_expected_widgets(): void
    {
        // Instantiate the page directly so we can inspect its widget registration.
        $page = app(AdminDashboard::class);

        $this->assertSame([AdminStatsWidget::class], $page->getWidgets());
        $this->assertSame(2, $page->getColumns());
        $this->assertSame('heroicon-o-home', AdminDashboard::getNavigationIcon());
    }
}
