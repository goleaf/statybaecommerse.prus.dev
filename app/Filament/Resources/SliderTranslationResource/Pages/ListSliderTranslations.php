<?php

declare(strict_types=1);

namespace App\Filament\Resources\SliderTranslationResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SliderTranslationResource;
use Filament\Actions;

final class ListSliderTranslations extends BaseListRecords
{
    
    protected static string $resource = SliderTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
