<?php declare(strict_types=1);

namespace App\Filament\Resources\NormalSettingResource\Pages;

use App\Filament\Resources\NormalSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListNormalSettings extends ListRecords
{
    protected static string $resource = NormalSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

