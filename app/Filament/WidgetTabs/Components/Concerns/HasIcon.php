<?php declare(strict_types=1);

namespace App\Filament\WidgetTabs\Components\Concerns;

use Filament\Support\Enums\IconSize;
use Closure;

trait HasIcon
{
    /**
     * @var string|(Closure(): string)|null
     */
    protected string|Closure|null $icon = null;

    /**
     * @var IconSize|string|(Closure(): (IconSize|string))|null
     */
    protected IconSize|string|Closure|null $iconSize = IconSize::Medium;

    /**
     * @param string|(Closure(): string)|null $icon
     */
    public function icon(string|Closure|null $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): ?string
    {
        /** @var string|null $icon */
        $icon = $this->evaluate($this->icon);

        return $icon;
    }

    /**
     * @param IconSize|string|(Closure(): (IconSize|string))|null $size
     */
    public function iconSize(IconSize|string|Closure|null $size): static
    {
        $this->iconSize = $size;

        return $this;
    }

    public function getIconSize(): IconSize|string|null
    {
        /** @var IconSize|string|null $size */
        $size = $this->evaluate($this->iconSize);

        if (is_string($size)) {
            // Try direct enum value match first (sm, md, lg, etc.)
            $enum = IconSize::tryFrom($size);

            if ($enum !== null) {
                return $enum;
            }

            // Map common string names to enum cases
            $enum = match (strtolower($size)) {
                'extra-small', 'extrasmall', 'xs' => IconSize::ExtraSmall,
                'small', 'sm' => IconSize::Small,
                'medium', 'md' => IconSize::Medium,
                'large', 'lg' => IconSize::Large,
                'extra-large', 'extralarge', 'xl' => IconSize::ExtraLarge,
                '2xl', 'two-extra-large', 'twoextralarge' => IconSize::TwoExtraLarge,
                default => null,
            };

            return $enum ?? $size;
        }

        return $size;
    }
}
