<?php

declare(strict_types=1);

namespace App\Filament\Pages\Support;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;

abstract class BaseListRecords extends ListRecords
{
    use ConfiguresToggleableTableLayout;
    use HasResizableColumns;
    use HasToggleableTable;

    /**
     * Configure the shared table instance for list pages before applying layout helpers.
     */
    public function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        $table = parent::table($table);

        return $this->applyToggleableTableLayout($table);
    }

    /**
     * Provide a backwards-compatible hook for Pest tests expecting `loadTable`.
     */
    public function loadTable(): void
    {
        // Touch the table records collection so Livewire interactions behave like Filament v3.
        $this->getTableRecords();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canViewAny(), 403);
    }

    /**
     * Bridge legacy Livewire test helpers that still call the `create` method directly.
     */
    public function create(): void
    {
        $activeActions = $this->mountedActions ?? [];

        if ($activeActions !== [] && ($activeActions[array_key_last($activeActions)]['name'] ?? null) === 'create') {
            $this->callMountedAction();

            return;
        }

        $this->mountAction('create');
    }
}
