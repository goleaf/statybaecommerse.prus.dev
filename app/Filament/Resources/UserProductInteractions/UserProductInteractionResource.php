<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractions;

use App\Filament\Resources\UserProductInteractions\Pages\CreateUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\EditUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\ListUserProductInteractions;
use App\Filament\Resources\UserProductInteractions\Schemas\UserProductInteractionForm;
use App\Filament\Resources\UserProductInteractions\Tables\UserProductInteractionsTable;
use App\Models\UserProductInteraction;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Schema;

class UserProductInteractionResource extends Resource
{
    protected static ?string $model = UserProductInteraction::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return Heroicon::OutlinedRectangleStack;
    }

    public static function form(Schema $form): Schema
    {
        // Ensure compatibility with the Schema-based form builder introduced in Filament v4.
        return UserProductInteractionForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return UserProductInteractionsTable::configure($table);
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
            'index' => ListUserProductInteractions::route('/'),
            'create' => CreateUserProductInteraction::route('/create'),
            'edit' => EditUserProductInteraction::route('/{record}/edit'),
        ];
    }
}
