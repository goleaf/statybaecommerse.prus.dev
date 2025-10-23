<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ActivityLogResource;

final class ListActivityLogs extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ActivityLogResource::class;
}
