<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\BrandResource;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListBrands extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        if (! BrandResource::canCreate()) {
            return [];
        }

        return [
            Actions\CreateAction::make()
                ->visible(fn () => AuthorizationMatrix::check('brands', 'create')),
        ];
    }
}
