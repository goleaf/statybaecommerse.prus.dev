<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCategoryResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NewsCategoryResource;
use Filament\Actions;

class ListNewsCategories extends BaseListRecords
{
    protected static string $resource = NewsCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
