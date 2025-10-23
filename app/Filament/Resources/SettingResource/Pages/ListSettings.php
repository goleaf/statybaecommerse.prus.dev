<?php

declare(strict_types=1);

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\SettingResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListSettings extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
