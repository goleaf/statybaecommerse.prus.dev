<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

final class GridLayoutColumn extends ViewColumn
{
    protected string $view = 'filament.tables.columns.grid-layout-column';

    /**
     * @var array<int, Column>
     */
    private array $sourceColumns = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('')
            ->toggleable(false)
            ->searchable(false)
            ->sortable(false)
            ->state(fn ($record) => $record);
    }

    /**
     * @param array<int, Column> $columns
     */
    public function sourceColumns(array $columns): static
    {
        $this->sourceColumns = $columns;

        return $this;
    }

    /**
     * @return array<int, Column>
     */
    public function getSourceColumns(): array
    {
        return $this->sourceColumns;
    }

    public function getTable(): Table
    {
        return parent::getTable();
    }
}
