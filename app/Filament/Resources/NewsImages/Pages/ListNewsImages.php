<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImages\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\NewsImages\NewsImageResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListNewsImages extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = NewsImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
