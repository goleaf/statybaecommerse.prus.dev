<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractions;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\UserProductInteractionResource as LegacyUserProductInteractionResource;
use App\Models\UserProductInteraction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Schema;

class UserProductInteractionResource extends Resource
{
    use HasNav;

    protected static ?string $model = UserProductInteraction::class;

    

    public static function getNavigationGroup(): ?string
    {
        return LegacyUserProductInteractionResource::getNavigationGroup();
    }

    public static function form(Schema $form): Schema
    {
        return LegacyUserProductInteractionResource::getNavigationLabel();
    }

    public static function getPluralModelLabel(): string
    {
        return LegacyUserProductInteractionResource::getPluralModelLabel();
    }

    public static function getModelLabel(): string
    {
        return LegacyUserProductInteractionResource::getModelLabel();
    }

    public static function form(Form $form): Form|array
    {
        return LegacyUserProductInteractionResource::form($form);
    }

    public static function table(Table $table): Table|array
    {
        return LegacyUserProductInteractionResource::table($table);
    }

    public static function getRelations(): array
    {
        return LegacyUserProductInteractionResource::getRelations();
    }

    public static function getPages(): array
    {
        return LegacyUserProductInteractionResource::getPages();
    }
}
