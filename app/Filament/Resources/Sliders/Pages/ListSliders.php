<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\Sliders\SliderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSliders extends ListRecords
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
