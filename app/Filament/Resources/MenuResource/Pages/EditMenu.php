<?php declare(strict_types=1);

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Resources\Pages\EditRecord;

final class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;
}
