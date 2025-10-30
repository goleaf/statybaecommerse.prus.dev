<?php

declare(strict_types=1);

namespace App\Support\Forms\Components;

use LaraZeus\MatrixChoice\Components\Matrix;

/**
 * Custom matrix component that keeps Filament from coercing checkbox state into scalar option lists.
 *
 * The base CheckboxList applies an OptionsArrayStateCast that turns nested arrays into strings, which
 * breaks the boolean row/column payload expected by the shipping matrix helpers. Overriding the
 * default state cast list allows the resource to attach its own normaliser so Livewire continues to
 * work with boolean grids while the UI renders checkbox selections normally.
 */
final class BooleanMatrix extends Matrix
{
    /**
     * Remove the default OptionsArrayStateCast inherited from CheckboxList so complex state casts can
     * manage the nested row/column structure without triggering array-to-string conversions.
     *
     * @return array<int, \Filament\Schemas\Components\StateCasts\Contracts\StateCast>
     */
    public function getDefaultStateCasts(): array
    {
        return [];
    }
}
