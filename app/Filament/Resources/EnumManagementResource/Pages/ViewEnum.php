<?php declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Resources\EnumManagementResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewEnum extends ViewRecord
{
    protected static string $resource = EnumManagementResource::class;
}
