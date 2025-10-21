<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractionResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\UserProductInteractionResource;
use Filament\Resources\Pages\ListRecords;

final class ListUserProductInteractions extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = UserProductInteractionResource::class;
}
