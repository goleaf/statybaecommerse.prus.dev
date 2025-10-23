<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeResource\Pages;

use App\Filament\Resources\ReferralCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable as SpatieTranslatableEditRecord;

final class EditReferralCode extends EditRecord
{
    use SpatieTranslatableEditRecord; // Synchronize translated attributes while editing records.

    protected static string $resource = ReferralCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Surface locale switching beside the edit actions.
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
