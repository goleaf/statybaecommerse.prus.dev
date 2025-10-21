<?php

declare(strict_types=1);

namespace App\Filament\WidgetTabs\Components\Concerns;

use Closure;
use Filament\Support\Enums\IconSize;

trait HasIcon
{
    protected string | Closure | null $icon = null;

    protected IconSize | string | Closure | null $iconSize = IconSize::Medium;

    public function icon(string | Closure | null $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->evaluate($this->icon);
    }

    public function iconSize(IconSize | string | Closure | null $size): static
    {
        $this->iconSize = $size;

        return $this;
    }

    public function getIconSize(): IconSize | string | null
    {
        $size = $this->evaluate($this->iconSize);

        if (is_string($size)) {
            return IconSize::tryFrom($size) ?? $size;
        }

        return $size;
    }
}
