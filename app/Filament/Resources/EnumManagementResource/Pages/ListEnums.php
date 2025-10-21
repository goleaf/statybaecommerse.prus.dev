<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\EnumManagementResource;
use Filament\Resources\Pages\ListRecords;

final class ListEnums extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = EnumManagementResource::class;
}
