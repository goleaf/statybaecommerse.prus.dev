<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\AdvancedReports;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(AdvancedReports::class)]
final class AdvancedReportsPageTest extends TestCase
{
    public function test_navigation_configuration_is_hidden_by_default(): void
    {
        // Assert the analytics tooling stays grouped and hidden unless toggled elsewhere.
        $this->assertSame('heroicon-o-chart-bar-square', AdvancedReports::getNavigationIcon());
        $this->assertSame('Analytics', AdvancedReports::getNavigationGroup());
        $this->assertSame('advanced-reports', AdvancedReports::getSlug());
        $this->assertFalse(AdvancedReports::shouldRegisterNavigation());
    }
}
