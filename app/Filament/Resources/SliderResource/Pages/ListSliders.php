<?php

declare(strict_types=1);

namespace App\Filament\Resources\SliderResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\SliderResource;
use Filament\Actions;

final class ListSliders extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
