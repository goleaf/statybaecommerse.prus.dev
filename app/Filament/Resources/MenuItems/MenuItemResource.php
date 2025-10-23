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
use Filament\Schemas\Schema;

class MenuItemResource extends Resource
{
    use HasNav;

    protected static ?string $model = MenuItem::class;

    /**
     * @var string|\BackedEnum|null Explicit icon string keeps Filament discovery compatible across PHP versions.
     */
    protected static $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        // Delegate to the dedicated schema helper so every consumer benefits from the tighter validation rules.
        return MenuItemForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // The table helper centralises shared configuration for Menu Item listings.
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
