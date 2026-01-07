<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers\Support;

use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use Closure;
// Support action-based schema entries in downstream implementations.
use Filament\Actions\Action;
// Allow grouped actions to appear within quick-edit schema arrays.
use Filament\Actions\ActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Component;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
use Illuminate\Contracts\Support\Htmlable;

abstract class BaseRelationManager extends RelationManager
{
    use ConfiguresToggleableTableLayout;
    use HasToggleableTable;

    public function table(Table $table): Table
    {
        // Configure the base relation manager table to align with Filament v4's required return type.
        $table = parent::table($table);

        return $this->applyToggleableTableLayout($table);
    }

    /**
     * Provide a sensible default so quick-edit repeaters can call this method safely when no override is supplied.
     *
     * @return array<int, Action|ActionGroup|Closure|Component|Htmlable|string>
     */
    protected function getQuickEditSchema(): array
    {
        // Defer to child classes to supply tailored schemas while keeping analyser expectations satisfied.
        return [];
    }
}
