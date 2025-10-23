<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\Sliders\SliderResource;
use Filament\Actions\CreateAction;

class ListSliders extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
