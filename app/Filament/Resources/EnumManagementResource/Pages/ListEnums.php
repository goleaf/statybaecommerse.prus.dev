<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\EnumManagementResource;
use App\Filament\Pages\Support\BaseListRecords;

final class ListEnums extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = EnumManagementResource::class;
}
