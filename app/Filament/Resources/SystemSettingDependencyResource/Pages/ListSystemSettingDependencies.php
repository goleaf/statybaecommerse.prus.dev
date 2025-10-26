<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingDependencyResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SystemSettingDependencyResource;
use Filament\Actions\CreateAction;

final class ListSystemSettingDependencies extends BaseListRecords
{
    protected static string $resource = SystemSettingDependencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
