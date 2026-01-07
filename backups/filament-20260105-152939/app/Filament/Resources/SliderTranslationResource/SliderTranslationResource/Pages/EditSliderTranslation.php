<?php

declare(strict_types=1);

namespace App\Filament\Resources\SliderTranslationResource\Pages;

use App\Filament\Resources\SliderTranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditSliderTranslation extends EditRecord
{
    protected static string $resource = SliderTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
