<?php

declare(strict_types=1);

namespace App\Support\FilamentCompat;

use Filament\Forms\Components\Select;

if (! class_exists(Select::class)) {
    return;
}

class Combobox extends Select
{
    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    public function boxSearchs(bool $condition = true): static
    {
        return $this;
    }

    public function optionsLabel(?string $label): static
    {
        return $this;
    }

    public function selectedLabel(?string $label): static
    {
        return $this;
    }

    public function height(string|int $value): static
    {
        return $this;
    }
}
