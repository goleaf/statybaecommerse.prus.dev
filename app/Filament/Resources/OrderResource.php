<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\Schemas\OrderForm;
use App\Filament\Resources\OrderResource\Schemas\OrderInfolist;
use App\Filament\Resources\OrderResource\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class OrderResource extends BaseResource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.orders');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.orders.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.orders.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager::class,
            \App\Filament\Resources\OrderResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\OrderResource\Widgets\OrderStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view'   => ViewOrder::route('/{record}'),
            'edit'   => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
