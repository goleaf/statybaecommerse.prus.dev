<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCategories;

use App\Filament\Resources\NewsCategories\Pages\CreateNewsCategory;
use App\Filament\Resources\NewsCategories\Pages\EditNewsCategory;
use App\Filament\Resources\NewsCategories\Pages\ListNewsCategories;
use App\Filament\Resources\NewsCategories\Schemas\NewsCategoryForm;
use App\Filament\Resources\NewsCategories\Tables\NewsCategoriesTable;
use App\Models\NewsCategory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

final class NewsCategoryResource extends Resource
{
    protected static ?string $model = NewsCategory::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'News';
    }

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return NewsCategoryForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
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
