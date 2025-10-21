<?php

declare(strict_types=1);

namespace App\Filament\Pages\Support;

use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use Filament\Resources\Pages\ManageRecords;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;

abstract class BaseManageRecords extends ManageRecords
{
    use ConfiguresToggleableTableLayout;
    use HasToggleableTable;

    public function table(Table $table): Table
    {
        // Ensure the base manage records page conforms to the Filament v4 table return type.
        $table = parent::table($table);

        return $this->applyToggleableTableLayout($table);
    }
}
