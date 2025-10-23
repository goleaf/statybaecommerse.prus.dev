<?php

declare(strict_types=1);

namespace App\Filament\Resources\SliderTranslationResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\SliderTranslationResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListSliderTranslations extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = SliderTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
