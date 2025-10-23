<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractionResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\UserProductInteractionResource;

final class ListUserProductInteractions extends BaseListRecords
{
    
    protected static string $resource = UserProductInteractionResource::class;
}
