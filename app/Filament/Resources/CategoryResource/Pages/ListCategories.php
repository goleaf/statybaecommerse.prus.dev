<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CategoryResource;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions;

final class ListCategories extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        if (! CategoryResource::canCreate()) {
            return [];
        }

        return [
            Actions\CreateAction::make()
                ->visible(fn () => AuthorizationMatrix::check('categories', 'create')),
        ];
    }
}
