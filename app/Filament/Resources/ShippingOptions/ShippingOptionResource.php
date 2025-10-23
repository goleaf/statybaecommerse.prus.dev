<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingOptions;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\ShippingOptions\Pages\CreateShippingOption;
use App\Filament\Resources\ShippingOptions\Pages\EditShippingOption;
use App\Filament\Resources\ShippingOptions\Pages\ListShippingOptions;
use App\Filament\Resources\ShippingOptions\Schemas\ShippingOptionForm;
use App\Filament\Resources\ShippingOptions\Tables\ShippingOptionsTable;
use App\Models\ShippingOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ShippingOptionResource extends Resource
{
    use HasNav;

    protected static ?string $model = ShippingOption::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {

        $form = $schema; // Preserve legacy variable naming for existing schema definitions.

        return ShippingOptionForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
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
            'index'  => ListShippingOptions::route('/'),
            'create' => CreateShippingOption::route('/create'),
            'edit'   => EditShippingOption::route('/{record}/edit'),
        ];
    }
}
