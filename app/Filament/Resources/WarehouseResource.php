<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Schemas\LocationForm;
use App\Filament\Resources\LocationResource\Schemas\LocationInfolist;
use App\Filament\Resources\WarehouseResource\Pages\CreateWarehouse;
use App\Filament\Resources\WarehouseResource\Pages\EditWarehouse;
use App\Filament\Resources\WarehouseResource\Pages\ListWarehouses;
use App\Filament\Resources\WarehouseResource\Pages\ViewWarehouse;
use App\Filament\Resources\WarehouseResource\Tables\WarehousesTable;
use App\Models\Location;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class WarehouseResource extends BaseResource
{
    protected static ?string $model = Location::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 16;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.warehouses');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.warehouses');
    }

    public static function getModelLabel(): string
    {
        return __('messages.warehouse');
    }

    public static function form(Schema $schema): Schema
    {
        return LocationForm::configure($schema, 'warehouse');
    }

    public static function infolist(Schema $schema): Schema
    {
        return LocationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarehousesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActiveScope::class,
                EnabledScope::class,
            ])
            ->where('type', 'warehouse');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'view'   => ViewWarehouse::route('/{record}'),
            'edit'   => EditWarehouse::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return self::getEloquentQuery();
    }
}
