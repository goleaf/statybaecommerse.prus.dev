<?php

declare(strict_types=1);

namespace App\Filament\Resources\CurrencyResource\Pages;

use App\Filament\Resources\CurrencyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable as SpatieTranslatableViewRecord;

final class ViewCurrency extends ViewRecord
{
    use SpatieTranslatableViewRecord; // Keep the detail view synchronized with the active locale.

    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Allow locale switching while reviewing record details.
            Actions\EditAction::make(),
        ];
    }
}
