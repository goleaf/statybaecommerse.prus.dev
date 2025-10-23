<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItems;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\MenuItems\Pages\CreateMenuItem;
use App\Filament\Resources\MenuItems\Pages\EditMenuItem;
use App\Filament\Resources\MenuItems\Pages\ListMenuItems;
use App\Filament\Resources\MenuItems\Schemas\MenuItemForm;
use App\Filament\Resources\MenuItems\Tables\MenuItemsTable;
use App\Models\MenuItem;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class MenuItemResource extends Resource
{
    use HasNav;

    protected static ?string $model = MenuItem::class;

    /**
     * @var string|\BackedEnum|null Menu resource icon aligned with Filament v4 docblock conventions.
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    /**
     * Build and return the Filament form definition for menu items.
     */
    public static function form(Form $form): Form
    {
        // Delegate the schema composition to the dedicated form configurator.
        return MenuItemForm::configure($form);
    }

    /**
     * Build and return the Filament table definition for menu items.
     */
    public static function table(Table $table): Table
    {
        // Delegate the column and action configuration to the shared table class.
        return MenuItemsTable::configure($table);
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
            'index'  => ListMenuItems::route('/'),
            'create' => CreateMenuItem::route('/create'),
            'edit'   => EditMenuItem::route('/{record}/edit'),
        ];
    }
}
