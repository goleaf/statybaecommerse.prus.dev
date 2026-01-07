<?php

declare(strict_types=1);

namespace App\Filament\Pages\Support;

use Filament\Resources\Pages\ManageRecords;
use Filament\Tables\Table;

abstract class BaseManageRecords extends ManageRecords
{
    public function table(Table $table): Table
    {
        $table = parent::table($table);

        return $this->applyToggleableTableLayout($table);
    }
}
