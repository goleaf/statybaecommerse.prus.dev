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
use BackedEnum;
use Filament\Schemas\Schema;
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

    public static function form(Schema $form): Schema
    {
        // Ensure compatibility with the Schema-based form builder introduced in Filament v4.
        return MenuItemForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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