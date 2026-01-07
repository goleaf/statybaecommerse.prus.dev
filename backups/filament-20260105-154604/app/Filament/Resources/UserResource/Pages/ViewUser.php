<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable as SpatieTranslatableViewRecord;

class ViewUser extends ViewRecord
{
    use SpatieTranslatableViewRecord; // Keep the detail view synchronized with the active locale.

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Allow locale switching while reviewing record details.
            EditAction::make(),
        ];
    }
}
