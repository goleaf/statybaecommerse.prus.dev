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
     * Ensure Livewire table helpers can hydrate the table before running assertions.
     */
    public function loadTable(): void
    {
        // Call the core Filament implementation so deferred loading flags are toggled.
        parent::loadTable();

        // Re-apply the toggleable layout to keep the component view in sync for tests.
        $this->applyToggleableTableLayout($this->getTable());
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
