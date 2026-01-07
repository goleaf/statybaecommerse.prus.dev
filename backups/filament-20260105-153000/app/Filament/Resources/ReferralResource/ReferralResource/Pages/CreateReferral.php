<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralResource\Pages;

use App\Filament\Resources\ReferralResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable as SpatieTranslatableCreateRecord;

final class CreateReferral extends CreateRecord
{
    use SpatieTranslatableCreateRecord; // Keep track of locale-specific form payloads during creation.

    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Allow admins to switch locales before entering translated values.
            ...parent::getHeaderActions(),
        ];
    }
}
