<?php

declare(strict_types=1);

namespace App\Filament\Tables\Concerns;

use App\Filament\Tables\Columns\GridLayoutColumn;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as ColumnLayoutComponent;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;

/**
 * @mixin HasToggleableTable
 */
trait ConfiguresToggleableTableLayout
{
    /**
     * @var array<int, Column | ColumnGroup | ColumnLayoutComponent> | null
     */
    protected ?array $toggleableTableListLayout = null;

    /**
     * @var array<int, Column> | null
     */
    protected ?array $toggleableTableListColumns = null;

    /**
     * @var array<string, int | null> | null
     */
    protected ?array $toggleableTableListContentGrid = null;

    protected function applyToggleableTableLayout(Table $table): Table
    {
        $columnsLayout = $table->getColumnsLayout();
        $visibleColumns = array_values($table->getVisibleColumns());
        $contentGrid = $table->getContentGrid();

        // Normalise the column layout to a simple sequential array so downstream checks
        // never trip over unexpected traversable implementations or sparse indexes.
        $normalizedColumnsLayout = is_array($columnsLayout)
            ? array_values($columnsLayout)
            : array_values(is_iterable($columnsLayout) ? iterator_to_array($columnsLayout) : []);

        $isCurrentlyGrid = (count($normalizedColumnsLayout) === 1)
            && $normalizedColumnsLayout[0] instanceof GridLayoutColumn;

        if (! $isCurrentlyGrid) {
            // Preserve the original layout components so we can restore the list view
            // after the user switches away from the grid presentation.
            $this->toggleableTableListLayout = $normalizedColumnsLayout === [] ? null : $normalizedColumnsLayout;
            $this->toggleableTableListColumns = $visibleColumns;
            $this->toggleableTableListContentGrid = $contentGrid;
        }

        $defaultGridConfiguration = [
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
        ];

        $table->contentGrid(function () use ($contentGrid, $defaultGridConfiguration): ?array {
            if ($this->isListLayout()) {
                return $this->toggleableTableListContentGrid ?? $contentGrid;
            }

            return $defaultGridConfiguration;
        });

        if ($this->isGridLayout()) {
            $listColumns = $this->toggleableTableListColumns;

            if ($listColumns === null || $listColumns === []) {
                return $table;
            }

            $table->columns([
                GridLayoutColumn::make('__grid_layout')
                    ->sourceColumns($listColumns),
            ]);

            return $table;
        }

        $listLayout = $this->toggleableTableListLayout;

        if ($listLayout !== null) {
            $table->columns($listLayout);
        }

        return $table;
    }
}
