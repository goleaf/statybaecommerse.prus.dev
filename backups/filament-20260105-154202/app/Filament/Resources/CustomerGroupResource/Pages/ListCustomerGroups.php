<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CustomerGroupResource;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as SpatieTranslatableListRecords;

final class ListCustomerGroups extends BaseListRecords
{
    use SpatieTranslatableListRecords;  // Track the active locale for listing translated records.

    protected static string $resource = CustomerGroupResource::class;

    protected function getTableQuery(): Builder|Relation|null
    {
        $query = parent::getTableQuery();

        if ($query instanceof Builder) {
            return $query->withoutGlobalScopes()->withTrashed();
        }

        if ($query instanceof Relation) {
            return $query->getQuery()->withoutGlobalScopes()->withTrashed();
        }

        return CustomerGroupResource::getEloquentQuery()->withoutGlobalScopes()->withTrashed();
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),  // Provide a quick language toggle for the grid view.
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        $table = parent::table($table);

        CustomerGroupResource::configureTable($table);

        return $table;
    }
}
