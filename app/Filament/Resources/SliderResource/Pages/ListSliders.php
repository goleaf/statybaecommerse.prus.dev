<?php

declare(strict_types=1);

namespace App\Filament\Resources\SliderResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\SliderResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

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
