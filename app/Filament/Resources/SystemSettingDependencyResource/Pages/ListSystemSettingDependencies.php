<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingDependencyResource\Pages;

use App\Filament\Resources\SystemSettingDependencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListSystemSettingDependencies extends ListRecords
{
    protected static string $resource = SystemSettingDependencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
