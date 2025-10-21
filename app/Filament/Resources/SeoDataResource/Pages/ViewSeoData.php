<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Pages;

use App\Filament\Resources\SeoDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable as SpatieTranslatableViewRecord;

final class ViewSeoData extends ViewRecord
{
    use SpatieTranslatableViewRecord; // Keep the detail view synchronized with the active locale.

    protected static string $resource = SeoDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Allow locale switching while reviewing record details.
            Actions\EditAction::make(),
        ];
    }
}
