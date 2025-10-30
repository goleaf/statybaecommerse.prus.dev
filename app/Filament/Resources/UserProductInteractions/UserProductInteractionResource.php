<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractions;

// Reuse the legacy resource so shared navigation, relations, and labels stay consistent during the v4 transition.
use App\Filament\Resources\UserProductInteractionResource as LegacyUserProductInteractionResource;
use App\Filament\Resources\UserProductInteractions\Pages\CreateUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\EditUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\ListUserProductInteractions;
use App\Filament\Resources\UserProductInteractions\Schemas\UserProductInteractionForm;
use App\Filament\Resources\UserProductInteractions\Tables\UserProductInteractionsTable;
use App\Models\UserProductInteraction;
use App\Support\Concerns\HasNav;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UserProductInteractionResource extends Resource
{
    use HasNav;

    protected static ?string $model = UserProductInteraction::class;

    public static function getSlug(?Panel $panel = null): string
    {
        // Avoid clashing with the legacy resource's routes/slug.
        return 'user-product-interactions-v4';
    }

    public static function form(Schema $schema): Schema
    {
        return UserProductInteractionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return UserProductInteractionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        // Delegate to the legacy resource so relation managers remain unified across both entry points.
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
