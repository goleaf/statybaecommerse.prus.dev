<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

// If your BaseListRecords added these traits, you can keep them here:
use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;

final class ListActivityLogs extends ListRecords
{
    // keep trait behavior that your BaseListRecords had
    use ConfiguresToggleableTableLayout;
    use HasResizableColumns;
    use HasToggleableTable;

    protected static string $resource = ActivityLogResource::class;

    // REQUIRED: instance table builder so Filament can construct $this->table
    public function table(Table $table): Table
    {
        return static::getResource()::table($table);
    }
}
