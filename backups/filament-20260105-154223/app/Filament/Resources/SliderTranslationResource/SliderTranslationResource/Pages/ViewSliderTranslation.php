<?php

declare(strict_types=1);

namespace App\Filament\Resources\SliderTranslationResource\Pages;

use App\Filament\Resources\SliderTranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewSliderTranslation extends ViewRecord
{
    protected static string $resource = SliderTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
