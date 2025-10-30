<?php

declare(strict_types=1);

namespace App\Support\Forms\Casts;

use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

/**
 * Cast matrix field state into a predictable boolean map keyed by rows/columns.
 */
final class MatrixBooleanStateCast implements StateCast
{
    /**
     * @param array<int, string> $rowKeys
     * @param array<int, string> $columnKeys
     */
    public function __construct(
        private readonly array $rowKeys,
        private readonly array $columnKeys,
    ) {
    }

    /**
     * Prepare dehydrated state before it is stored to the database.
     */
    public function set(mixed $state): array
    {
        return $this->normalize($state);
    }

    /**
     * Rehydrate stored state so Livewire receives boolean selections.
     */
    public function get(mixed $state): array
    {
        return $this->normalize($state);
    }

    /**
     * Convert any matrix payload shape into a row/column boolean grid.
     *
     * @return array<string, array<string, bool>>
     */
    private function normalize(mixed $state): array
    {
        $stateArray = is_array($state) ? $state : [];

        $normalized = [];

        foreach ($this->rowKeys as $rowKey) {
            $rowState = $stateArray[$rowKey] ?? [];
            $normalized[$rowKey] = [];

            foreach ($this->columnKeys as $columnKey) {
                $value = false;

                if (is_array($rowState)) {
                    if (array_is_list($rowState)) {
                        $value = in_array($columnKey, $rowState, true);
                    } else {
                        $value = (bool) ($rowState[$columnKey] ?? false);
                    }
                }

                $normalized[$rowKey][$columnKey] = $value;
            }
        }

        return $normalized;
    }
}
