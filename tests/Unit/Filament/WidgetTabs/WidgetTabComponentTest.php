<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\WidgetTabs;

use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Enums\WidgetTabTheme;
use Filament\Support\Enums\IconSize;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Tests\TestCase;

final class WidgetTabComponentTest extends TestCase
{
    protected function tearDown(): void
    {
        // Close Mockery so expectation assertions stay isolated per test case.
        Mockery::close();

        parent::tearDown();
    }

    public function test_widget_tab_configures_label_value_and_theme_classes(): void
    {
        // Arrange: build a widget tab with multiple visual and numerical attributes.
        $tab = WidgetTab::make('Total orders')
            ->value(125.5)
            ->precision(1)
            ->percentage(true)
            ->percentagePrecision(2)
            ->icon('heroicon-o-shopping-cart')
            ->iconSize(IconSize::Large)
            ->theme(WidgetTabTheme::Success)
            ->gradient()
            ->customThemeClasses(['shadow-md']);

        // Assert: the configured tab exposes every attribute through its accessors.
        expect($tab->getLabel())->toBe('Total orders')
            ->and($tab->getValue())->toBe(125.5)
            ->and($tab->getPrecision())->toBe(1)
            ->and($tab->isPercentage())->toBeTrue()
            ->and($tab->getPercentagePrecision())->toBe(2)
            ->and($tab->getIcon())->toBe('heroicon-o-shopping-cart')
            ->and($tab->getIconSize())->toBe(IconSize::Large)
            ->and($tab->getThemeClasses())
            ->toContain('fi-widget-tab-success')
            ->toContain('fi-widget-tab-gradient')
            ->toContain('shadow-md');
    }

    public function test_widget_tab_supports_lazy_configuration_and_icon_size_strings(): void
    {
        // Arrange: configure the widget tab with closures and a string-based icon size.
        $tab = WidgetTab::make(static fn (): string => 'Dynamic label')
            ->value(static fn (): int => 42)
            ->iconSize('small')
            ->theme(static fn (): WidgetTabTheme => WidgetTabTheme::Info)
            ->customThemeClasses(static fn (): array => ['custom-class']);

        // Assert: closures are evaluated lazily and string icon sizes resolve to enums.
        expect($tab->getLabel())->toBe('Dynamic label')
            ->and($tab->getValue())->toBe(42)
            ->and($tab->getIconSize())->toBe(IconSize::Small)
            ->and($tab->getThemeClasses())
            ->toContain('fi-widget-tab-info')
            ->toContain('custom-class');

        // Act: switch to a non-standard icon size string to confirm passthrough behaviour.
        $tab->iconSize('very-large');

        // Assert: unknown strings are returned untouched for downstream styling hooks.
        expect($tab->getIconSize())->toBe('very-large');
    }

    public function test_widget_tab_modify_query_passes_builder_to_callback(): void
    {
        // Arrange: prime the widget tab with a query callback that tracks invocations.
        $tab = WidgetTab::make('Sales');
        $wasInvoked = false;

        $tab->query(static function (Builder $builder) use (&$wasInvoked): Builder {
            // Record that the callback executed and return the builder unchanged.
            $wasInvoked = true;

            return $builder;
        });

        // Act: resolve the builder through the widget tab modifier.
        $builder = Mockery::mock(Builder::class);
        $builder->shouldIgnoreMissing();

        $result = $tab->modifyQuery($builder);

        // Assert: the callback executed and returned the original builder instance.
        expect($wasInvoked)->toBeTrue()
            ->and($result)->toBe($builder);
    }
}
