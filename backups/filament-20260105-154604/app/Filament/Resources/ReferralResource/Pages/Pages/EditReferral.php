<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralResource\Pages;

use App\Filament\Resources\ReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable as SpatieTranslatableEditRecord;

final class EditReferral extends EditRecord
{
    use SpatieTranslatableEditRecord; // Synchronize translated attributes while editing records.

    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Surface locale switching beside the edit actions.
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
