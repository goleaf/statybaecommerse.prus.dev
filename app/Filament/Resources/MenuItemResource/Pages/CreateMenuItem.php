<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateMenuItem extends CreateRecord
{
    protected static string $resource = MenuItemResource::class;
}
