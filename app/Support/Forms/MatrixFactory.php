<?php

declare(strict_types=1);

namespace App\Support\Forms;

use App\Support\Forms\Casts\MatrixBooleanStateCast;
use App\Support\Forms\Components\BooleanMatrix;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;

final class MatrixFactory
{
    /**
     * Build a section containing module permission toggles.
     *
     * @param array<string, array<string, string>> $definition
     * @param callable(string): string             $moduleLabelResolver
     * @param callable(string): string             $abilityLabelResolver
     */
    public static function permissions(
        array $definition,
        callable $moduleLabelResolver,
        callable $abilityLabelResolver,
        string $sectionLabel,
        string $statePath = 'permissions_matrix'
    ): Section {
        $moduleSections = [];

        foreach ($definition as $module => $actions) {
            if (! is_string($module) || $module === '' || ! is_array($actions) || $actions === []) {
                continue;
            }

            $toggles = [];

            foreach (array_keys($actions) as $action) {
                if (! is_string($action) || $action === '') {
                    continue;
                }

                $label = (string) $abilityLabelResolver($action);

                if ($label === '') {
                    $label = $action;
                }

                $toggles[] = Toggle::make(sprintf('%s.%s', $module, $action))
                    ->label($label)
                    ->default(false);
            }

            if ($toggles === []) {
                continue;
            }

            $moduleLabel = (string) $moduleLabelResolver($module);

            if ($moduleLabel === '') {
                $moduleLabel = $module;
            }

            $moduleSections[] = Section::make($moduleLabel)
                ->schema([
                    Grid::make(max(1, min(4, count($toggles))))
                        ->schema($toggles),
                ])
                ->collapsible(false);
        }

        $sectionLabel = $sectionLabel !== '' ? $sectionLabel : 'Permissions';

        return Section::make($sectionLabel)
            ->schema($moduleSections)
            ->statePath($statePath)
            ->columns(1)
            ->collapsible();
    }

    /**
     * Build a radio-based grid for mapping rows to a single selectable column.
     *
     * The row resolver should return an array or collection of row definitions with keys:
     * - key: unique identifier for the row (used in the state path)
     * - label: display label
     * - options: array of column key => label pairs
     * - hint (optional): helper text for the row
     *
     * @param callable(Get $get): (array<int, array<string, mixed>>|Collection<int, array<string, mixed>>) $rowsResolver
     */
    public static function radioGrid(string $statePath, callable $rowsResolver): Component
    {
        return self::buildDynamicGrid($statePath, $rowsResolver, function (string $fieldName, array $row): Component {
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
     *
     * @param array<string, string> $rows
     * @param array<string, string> $columns
     */
    public static function checkboxGrid(string $name, array $rows, array $columns): BooleanMatrix
    {
        $rowKeys = array_keys($rows);
        $columnKeys = array_keys($columns);
        $stateCast = new MatrixBooleanStateCast($rowKeys, $columnKeys);

        return BooleanMatrix::make($name)
            ->rowData($rows)
            ->columnData($columns)
            ->asCheckbox()
            // Allow the matrix to be submitted without selecting every row so default
            // channel creation flows (and automated tests) are not blocked by validation.
            ->rowSelectRequired(false)
            // Prevent automatic dehydration so resources can manage persistence manually.
            ->dehydrated(false)
            // Disable the default checkbox list validation since matrix rows are handled manually.
            ->rules([])
            // Skip validation hooks since persistence is handled manually by the resource pages.
            ->validatedWhenNotDehydrated(false)
            // Apply a bespoke state cast so Livewire always works with a boolean grid.
            ->stateCast($stateCast)
            // Ensure null or sparse payloads are normalised before hitting the UI bindings.
            ->formatStateUsing(fn (mixed $state): array => $stateCast->get($state))
            // Keep the stored state aligned with the boolean grid representation even when
            // Livewire submits partial checkbox payloads during interaction.
            ->dehydrateStateUsing(fn (mixed $state): array => $stateCast->set($state))
            // Provide a predictable default payload so Livewire bindings never operate on null.
            ->default(static fn (): array => $stateCast->set([]));
    }

    /**
     * @param callable(string, array<string, mixed>): Component                                            $fieldFactory
     * @param callable(Get $get): (array<int, array<string, mixed>>|Collection<int, array<string, mixed>>) $rowsResolver
     */
    private static function buildDynamicGrid(string $statePath, callable $rowsResolver, callable $fieldFactory): Component
    {
        return Grid::make()
            ->columns(1)
            ->schema(static function (Get $get) use ($statePath, $rowsResolver, $fieldFactory): array {
                $rows = $rowsResolver($get);
                $rows = $rows instanceof Collection ? $rows : collect($rows ?? []);

                if ($rows->isEmpty()) {
                    return [
                        Placeholder::make($statePath . '_empty')
                            ->label(__('No attributes available'))
                            ->content(__('Assign attributes to the product to configure the matrix.')),
                    ];
                }

                return $rows
                    ->map(static function (array $row) use ($statePath, $fieldFactory): Component {
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
