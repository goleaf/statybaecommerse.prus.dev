<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingDependencyResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\SystemSettingDependencyResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

final class ListSystemSettingDependencies extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = SystemSettingDependencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
