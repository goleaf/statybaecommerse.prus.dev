<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ActivityLogResource;

final class ListActivityLogs extends BaseListRecords
{
    protected static string $resource = ActivityLogResource::class;
}
