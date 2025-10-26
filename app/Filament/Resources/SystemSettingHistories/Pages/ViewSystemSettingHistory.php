<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistories\Pages;

use App\Filament\Resources\SystemSettingHistories\SystemSettingHistoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSystemSettingHistory extends ViewRecord
{
    protected static string $resource = SystemSettingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
