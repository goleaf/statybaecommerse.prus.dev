<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistoryResource\Pages;

use App\Filament\Resources\SystemSettingHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListSystemSettingHistories extends ListRecords
{
    protected static string $resource = SystemSettingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
