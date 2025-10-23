<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCategories\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NewsCategories\NewsCategoryResource;
use Filament\Actions\CreateAction;

final class ListNewsCategories extends BaseListRecords
{
    
    protected static string $resource = NewsCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
