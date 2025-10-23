<?php

declare(strict_types=1);

namespace App\Filament\Pages\Support;

use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;

abstract class BaseListRecords extends ListRecords
{
    use ConfiguresToggleableTableLayout;
    use HasResizableColumn;
    use HasToggleableTable;

    public function table(Table $table): Table|array
    {
        $table = parent::table($table);

        return $this->applyToggleableTableLayout($table);
    }
}
