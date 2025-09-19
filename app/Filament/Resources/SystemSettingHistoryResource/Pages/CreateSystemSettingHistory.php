<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistoryResource\Pages;

use App\Filament\Resources\SystemSettingHistoryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSystemSettingHistory extends CreateRecord
{
    protected static string $resource = SystemSettingHistoryResource::class;
}
