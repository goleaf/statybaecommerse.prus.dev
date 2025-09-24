<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippings;

use App\Filament\Resources\OrderShippings\Pages\CreateOrderShipping;
use App\Filament\Resources\OrderShippings\Pages\EditOrderShipping;
use App\Filament\Resources\OrderShippings\Pages\ListOrderShippings;
use App\Filament\Resources\OrderShippings\Schemas\OrderShippingForm;
use App\Filament\Resources\OrderShippings\Tables\OrderShippingsTable;
use App\Models\OrderShipping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class OrderShippingResource extends Resource
{
    protected static ?string $model = OrderShipping::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return Heroicon::OutlinedRectangleStack;
    }

    public static function form(Schema $schema): Schema
    {
        return OrderShippingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderShippingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderShippings::route('/'),
            'create' => CreateOrderShipping::route('/create'),
            'edit' => EditOrderShipping::route('/{record}/edit'),
        ];
    }
}
