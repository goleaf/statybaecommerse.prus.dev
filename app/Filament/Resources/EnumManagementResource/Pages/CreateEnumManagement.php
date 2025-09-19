<?php declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Resources\EnumManagementResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;

class CreateEnumManagement extends CreateRecord
{
    protected static string $resource = EnumManagementResource::class;
}
