<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItems;
use BackedEnum;
use App\Support\Concerns\HasNav;


use Filament\Schemas\Schema;
use App\Filament\Resources\MenuItems\Pages\CreateMenuItem;
use App\Filament\Resources\MenuItems\Pages\EditMenuItem;
use App\Filament\Resources\MenuItems\Pages\ListMenuItems;
use App\Filament\Resources\MenuItems\Schemas\MenuItemForm;
use App\Filament\Resources\MenuItems\Tables\MenuItemsTable;
use App\Models\MenuItem;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    use HasNav;

    protected static ?string $model = MenuItem::class;

    /**
     * @var string|\BackedEnum|null Menu item icon aligned with Filament v4 guidance.
     */
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    /**
     * Compose the shared form schema for creating and editing menu items.
     */
    public static function form(Schema $schema): Schema
    {
        // Delegate field layout to the dedicated schema configurator for reuse.
        return MenuItemForm::configure($schema);
    }

    /**
     * Build the reusable table definition for listing menu items.
     */
    public static function table(Table $table): Table
    {
        // Centralise column configuration in the shared table builder.
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
