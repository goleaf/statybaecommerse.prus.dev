<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistoryResource\Pages;

use App\Filament\Resources\SystemSettingHistoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewSystemSettingHistory extends ViewRecord
{
    protected static string $resource = SystemSettingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
