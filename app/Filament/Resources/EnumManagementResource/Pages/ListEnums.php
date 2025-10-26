<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\EnumManagementResource;

final class ListEnums extends BaseListRecords
{
    protected static string $resource = EnumManagementResource::class;
}
