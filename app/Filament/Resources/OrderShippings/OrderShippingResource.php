<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippings;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\OrderShippings\Pages\CreateOrderShipping;
use App\Filament\Resources\OrderShippings\Pages\EditOrderShipping;
use App\Filament\Resources\OrderShippings\Pages\ListOrderShippings;
use App\Filament\Resources\OrderShippings\Schemas\OrderShippingForm;
use App\Filament\Resources\OrderShippings\Tables\OrderShippingsTable;
use App\Models\OrderShipping;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Schema;

class OrderShippingResource extends Resource
{
    use HasNav;

    protected static ?string $model = OrderShipping::class;

    public static function getNavigationIcon(): BackedEnum|\UnitEnum|Htmlable|string|null
    {
        return Heroicon::OutlinedRectangleStack;
    }

    public static function form(Schema $schema): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return OrderShippingForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
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
            'index'  => ListOrderShippings::route('/'),
            'create' => CreateOrderShipping::route('/create'),
            'edit'   => EditOrderShipping::route('/{record}/edit'),
        ];
    }
}