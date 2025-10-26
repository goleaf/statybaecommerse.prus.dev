<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers\Support;

use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;

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
}
