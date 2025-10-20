<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingOptions;

use App\Filament\Resources\ShippingOptions\Pages\CreateShippingOption;
use App\Filament\Resources\ShippingOptions\Pages\EditShippingOption;
use App\Filament\Resources\ShippingOptions\Pages\ListShippingOptions;
use App\Filament\Resources\ShippingOptions\Schemas\ShippingOptionForm;
use App\Filament\Resources\ShippingOptions\Tables\ShippingOptionsTable;
use App\Models\ShippingOption;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShippingOptionResource extends Resource
{
    protected static ?string $model = ShippingOption::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        return ShippingOptionForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return ShippingOptionsTable::configure($table);
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
            'index' => ListShippingOptions::route('/'),
            'create' => CreateShippingOption::route('/create'),
            'edit' => EditShippingOption::route('/{record}/edit'),
        ];
    }
}
