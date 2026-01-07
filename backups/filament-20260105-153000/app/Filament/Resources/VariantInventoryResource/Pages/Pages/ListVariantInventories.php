<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantInventoryResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\VariantInventoryResource;
use Filament\Actions\CreateAction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class ListVariantInventories extends BaseListRecords
{
    protected static string $resource = VariantInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * Keep partner inventory pagination links aligned with the active query parameters.
     */
    protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
    {
        // Call into the shared list pagination logic before mutating the query string handling.
        $paginator = parent::paginateTableQuery($query);

        // Append filters from the request so paging through partner inventory preserves context.
        if (method_exists($paginator, 'withQueryString')) {
            return $paginator->withQueryString();
        }

        return $paginator;
    }
}
