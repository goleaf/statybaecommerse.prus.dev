<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCategories;
use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NewsCategories\Pages\CreateNewsCategory;
use App\Filament\Resources\NewsCategories\Pages\EditNewsCategory;
use App\Filament\Resources\NewsCategories\Pages\ListNewsCategories;
use App\Filament\Resources\NewsCategories\Schemas\NewsCategoryForm;
use App\Filament\Resources\NewsCategories\Tables\NewsCategoriesTable;
use App\Models\NewsCategory;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Schemas\Schema;

final class NewsCategoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = NewsCategory::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * @var string|BackedEnum|null Keep the resource grouped with other news modules.
     */
    protected static $navigationGroup = NavigationGroup::News;

    public static function getNavigationGroup(): ?string
    {
        // Delegate to the enum label for localisation support.
        $group = self::$navigationGroup;

        return $group instanceof NavigationGroup ? $group->label() : $group;
    }

    public static function form(Schema $form): Schema
    {
        // Ensure compatibility with the Schema-based form builder introduced in Filament v4.
        return NewsCategoryForm::configure($form);
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