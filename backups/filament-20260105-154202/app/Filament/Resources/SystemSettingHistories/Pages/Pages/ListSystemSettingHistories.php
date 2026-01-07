<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistories\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SystemSettingHistories\SystemSettingHistoryResource;
use Filament\Actions\CreateAction;

class ListSystemSettingHistories extends BaseListRecords
{
    protected static string $resource = SystemSettingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
