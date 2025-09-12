<?php declare(strict_types=1);

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Resources\Pages\ListRecords;

final class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;
}
