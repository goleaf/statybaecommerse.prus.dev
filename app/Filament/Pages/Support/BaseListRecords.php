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

    public function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        $table = parent::table($table);

        return $this->applyToggleableTableLayout($table);
    }
}