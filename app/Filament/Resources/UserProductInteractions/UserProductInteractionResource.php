<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractions;

use App\Filament\Resources\UserProductInteractions\Pages\CreateUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\EditUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\ListUserProductInteractions;
use App\Filament\Resources\UserProductInteractions\Schemas\UserProductInteractionForm;
use App\Filament\Resources\UserProductInteractions\Tables\UserProductInteractionsTable;
use App\Models\UserProductInteraction;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class UserProductInteractionResource extends Resource
{
    use HasNav;

    protected static ?string $model = UserProductInteraction::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        // Defer to the legacy resource for consistent icon configuration, if available.
        return \App\Filament\Resources\UserProductInteractionResource::getNavigationGroup();
    }

    public static function getSlug(): string
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
