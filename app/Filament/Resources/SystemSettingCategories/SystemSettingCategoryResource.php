<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategories;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\SystemSettingCategories\Pages\CreateSystemSettingCategory;
use App\Filament\Resources\SystemSettingCategories\Pages\EditSystemSettingCategory;
use App\Filament\Resources\SystemSettingCategories\Pages\ListSystemSettingCategories;
use App\Filament\Resources\SystemSettingCategories\Schemas\SystemSettingCategoryForm;
use App\Filament\Resources\SystemSettingCategories\Tables\SystemSettingCategoriesTable;
use App\Models\SystemSettingCategory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Schema;

class SystemSettingCategoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = SystemSettingCategory::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return SystemSettingCategoryForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return SystemSettingCategoriesTable::configure($table);
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
            'index'  => ListSystemSettingCategories::route('/'),
            'create' => CreateSystemSettingCategory::route('/create'),
            'edit'   => EditSystemSettingCategory::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
