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
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    use HasNav;

    protected static ?string $model = MenuItem::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form|array
    {
        // Delegate field layout to the dedicated schema configurator for reuse.
        return MenuItemForm::configure($form);
    }

    public static function table(Table $table): Table|array
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
