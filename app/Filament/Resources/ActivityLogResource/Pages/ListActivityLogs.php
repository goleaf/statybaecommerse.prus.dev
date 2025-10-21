<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

final class ListActivityLogs extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = ActivityLogResource::class;
}
