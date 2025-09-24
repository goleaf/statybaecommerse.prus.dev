<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserPreferenceResource\Pages;

use App\Filament\Resources\UserPreferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
