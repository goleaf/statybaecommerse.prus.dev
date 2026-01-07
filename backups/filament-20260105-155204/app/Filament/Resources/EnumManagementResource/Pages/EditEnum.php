<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Resources\EnumManagementResource;
use Filament\Resources\Pages\EditRecord;

final class EditEnum extends EditRecord
{
    protected static string $resource = EnumManagementResource::class;
}
