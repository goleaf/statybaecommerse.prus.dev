<?php

declare(strict_types=1);

namespace App\Filament\WidgetTabs\Components\Concerns;

use App\Filament\WidgetTabs\Enums\WidgetTabTheme;
use Closure;

trait HasTheme
{
    protected WidgetTabTheme|string|Closure|null $theme = null;

    protected bool|Closure $useGradient = false;

    protected array|Closure $customThemeClasses = [];

    public function theme(WidgetTabTheme|string|Closure|null $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function gradient(bool|Closure $condition = true): static
    {
        $this->useGradient = $condition;

        return $this;
    }

    public function customThemeClasses(array|Closure $classes): static
    {
        $this->customThemeClasses = $classes;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getThemeClasses(): array
    {
        $theme = $this->evaluate($this->theme);
        $classes = [];

        if ($theme instanceof WidgetTabTheme) {
            $theme = $theme->value;
        }

        if (is_string($theme) && $theme !== '') {
            $classes[] = sprintf('fi-widget-tab-%s', $theme);
        }

        if ($this->evaluate($this->useGradient)) {
            $classes[] = 'fi-widget-tab-gradient';
        }

        return array_merge($classes, $this->evaluate($this->customThemeClasses));
    }
}
