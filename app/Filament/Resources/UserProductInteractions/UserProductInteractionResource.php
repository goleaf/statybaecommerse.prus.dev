<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractions;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\UserProductInteractionResource as LegacyUserProductInteractionResource;
use App\Models\UserProductInteraction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Schema;

class UserProductInteractionResource extends Resource
{
    use HasNav;

    protected static ?string $model = UserProductInteraction::class;

    public static function getNavigationIcon(): BackedEnum|\UnitEnum|Htmlable|string|null
    {
        return LegacyUserProductInteractionResource::getNavigationGroup();
    }

    public static function form(Schema $form): Schema
    {

        $form = $schema; // Preserve legacy variable naming for existing schema definitions.

        return UserProductInteractionForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return UserProductInteractionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return LegacyUserProductInteractionResource::getRelations();
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUserProductInteractions::route('/'),
            'create' => CreateUserProductInteraction::route('/create'),
            'edit'   => EditUserProductInteraction::route('/{record}/edit'),
        ];
    }
}
