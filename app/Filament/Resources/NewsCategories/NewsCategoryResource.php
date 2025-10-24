<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCategories;

use App\Enums\NavigationGroup;
use App\Support\Concerns\HasNav;
use Filament\Schemas\Schema;
use App\Filament\Resources\NewsCategories\Pages\CreateNewsCategory;
use App\Filament\Resources\NewsCategories\Pages\EditNewsCategory;
use App\Filament\Resources\NewsCategories\Pages\ListNewsCategories;
use App\Filament\Resources\NewsCategories\Schemas\NewsCategoryForm;
use App\Filament\Resources\NewsCategories\Tables\NewsCategoriesTable;
use App\Models\NewsCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

final class NewsCategoryResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * @var string|BackedEnum|null Keep the resource grouped with other news modules.
     */
    protected static \UnitEnum|string|null $navigationGroup = NavigationGroup::News->value;

    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        // Delegate to the enum label for localisation support.
        $group = self::$navigationGroup;

        return $group instanceof NavigationGroup ? $group->label() : $group;
    }

    public static function form(Schema $schema): Schema   
    {
        return NewsCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return NewsCategoriesTable::configure($table);
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
            'index'  => ListNewsCategories::route('/'),
            'create' => CreateNewsCategory::route('/create'),
            'edit'   => EditNewsCategory::route('/{record}/edit'),
        ];
    }
}
