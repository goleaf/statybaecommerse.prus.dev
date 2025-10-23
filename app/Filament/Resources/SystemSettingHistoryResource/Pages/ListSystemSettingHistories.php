<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistoryResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\SystemSettingHistoryResource;
use Filament\Actions\CreateAction;

final class ListSystemSettingHistories extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = SystemSettingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
