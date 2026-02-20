<?php

declare(strict_types=1);

namespace App\Support\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

final class SortOrderInput
{
    public static function make(string $name = 'sort_order', ?string $label = null): TextInput
    {
        return TextInput::make($name)
            ->label($label ?? __('messages.sort_order'))
            ->numeric()
            ->integer()
            ->default(0)
            ->minValue(0)
            ->step(1)
            ->inputMode('numeric')
            ->suffixIcon(Heroicon::Bars3BottomLeft)
            ->suffixIconColor('gray')
            ->helperText(__('translations.sort_order_help'));
    }
}
