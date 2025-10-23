<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Get;
use Illuminate\Support\Collection;

final class MatrixFactory
{
    /**
     * Build a radio-based grid for mapping rows to a single selectable column.
     *
     * The row resolver should return an array or collection of row definitions with keys:
     * - key: unique identifier for the row (used in state path)
     * - label: display label
     * - options: array of column key => label pairs
     * - hint (optional): helper text for the row
     */
    public static function radioGrid(string $statePath, callable $rowsResolver): Component
    {
        return self::buildGrid($statePath, $rowsResolver, function (string $fieldName, array $row): Component {
            $radio = Radio::make($fieldName)
                ->label($row['label'] ?? $fieldName)
                ->options($row['options'] ?? [])
                ->inline()
                ->inlineLabel(false)
                ->nullable();

            if (! empty($row['hint'])) {
                $radio->hint($row['hint']);
            }

            return $radio;
        });
    }

    /**
     * Build a checkbox-based grid for mapping rows to multiple selectable columns.
     */
    public static function checkboxGrid(string $statePath, callable $rowsResolver): Component
    {
        return self::buildGrid($statePath, $rowsResolver, function (string $fieldName, array $row): Component {
            $options = $row['options'] ?? [];
            $checkboxes = CheckboxList::make($fieldName)
                ->label($row['label'] ?? $fieldName)
                ->options($options)
                ->columns(min(max(count($options), 1), 4));

            if (! empty($row['hint'])) {
                $checkboxes->hint($row['hint']);
            }

            return $checkboxes;
        });
    }

    /**
     * @param  callable(Get $get): (array<int, array<string, mixed>>|Collection<int, array<string, mixed>>)  $rowsResolver
     * @param  callable(string, array<string, mixed>): Component  $fieldFactory
     */
    private static function buildGrid(string $statePath, callable $rowsResolver, callable $fieldFactory): Component
    {
        return Grid::make()
            ->columns(1)
            ->schema(function (Get $get) use ($statePath, $rowsResolver, $fieldFactory): array {
                $rows = $rowsResolver($get);
                $rows = $rows instanceof Collection ? $rows : collect($rows ?? []);

                if ($rows->isEmpty()) {
                    return [
                        Placeholder::make($statePath.'_empty')
                            ->label(__('No attributes available'))
                            ->content(__('Assign attributes to the product to configure the matrix.')),
                    ];
                }

                return $rows
                    ->map(function (array $row) use ($statePath, $fieldFactory): Component {
                        $key = $row['key'] ?? $row['attribute_id'] ?? null;
                        $fieldName = is_string($key) && str_contains($key, '.')
                            ? $key
                            : sprintf('%s.%s', $statePath, $key ?? uniqid('row_', true));

                        return $fieldFactory($fieldName, $row);
                    })
                    ->values()
                    ->all();
            });
    }
}
