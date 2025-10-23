<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingDependencies\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SystemSettingDependencies\SystemSettingDependencyResource;
use Filament\Actions\CreateAction;

class ListSystemSettingDependencies extends BaseListRecords
{
    
    protected static string $resource = SystemSettingDependencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
