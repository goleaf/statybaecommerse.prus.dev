<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImageResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\NewsImageResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

class ListNewsImages extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = NewsImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
