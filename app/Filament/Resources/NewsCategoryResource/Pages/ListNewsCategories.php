<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCategoryResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\NewsCategoryResource;
use Filament\Actions;

class ListNewsCategories extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = NewsCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
