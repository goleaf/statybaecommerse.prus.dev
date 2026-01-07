<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Resources\EnumManagementResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateEnum extends CreateRecord
{
    protected static string $resource = EnumManagementResource::class;
}
