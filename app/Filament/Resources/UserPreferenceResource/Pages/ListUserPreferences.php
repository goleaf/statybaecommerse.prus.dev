<?php declare(strict_types=1);

namespace App\Filament\Resources\UserPreferenceResource\Pages;

use App\Filament\Resources\UserPreferenceResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListUserPreferences extends ListRecords
{
    protected static string $resource = UserPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

