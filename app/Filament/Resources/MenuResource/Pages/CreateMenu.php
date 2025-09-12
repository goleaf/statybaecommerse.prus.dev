<?php declare(strict_types=1);

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;
}
