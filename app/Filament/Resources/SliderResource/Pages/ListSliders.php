<?php declare(strict_types=1);

namespace App\Filament\Resources\SliderResource\Pages;

use App\Filament\Resources\SliderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSliders extends ListRecords
{
    protected static string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
