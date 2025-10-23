<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractionResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\UserProductInteractionResource;
use App\Filament\Pages\Support\BaseListRecords;

final class ListUserProductInteractions extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = UserProductInteractionResource::class;
}
