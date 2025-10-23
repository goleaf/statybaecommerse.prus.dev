<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserPreferenceResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\UserPreferenceResource;
use Filament\Actions;

final class ListUserPreferences extends BaseListRecords
{
    
    protected static string $resource = UserPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
