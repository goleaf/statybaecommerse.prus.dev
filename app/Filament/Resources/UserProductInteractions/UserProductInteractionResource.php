<?php

namespace App\Filament\Resources\UserProductInteractions;
use App\Filament\Resources\UserProductInteractions\Pages\CreateUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\EditUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\ListUserProductInteractions;
use App\Filament\Resources\UserProductInteractions\Schemas\UserProductInteractionForm;
use App\Filament\Resources\UserProductInteractions\Tables\UserProductInteractionsTable;
use App\Models\UserProductInteraction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
class UserProductInteractionResource extends Resource
{
    protected static ?string $model = UserProductInteraction::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function form(Schema $schema): Schema
    {
        return UserProductInteractionForm::configure($schema);
    }
    public static function table(Table $table): Table
        return UserProductInteractionsTable::configure($table);
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => ListUserProductInteractions::route('/'),
            'create' => CreateUserProductInteraction::route('/create'),
            'edit' => EditUserProductInteraction::route('/{record}/edit'),
}
