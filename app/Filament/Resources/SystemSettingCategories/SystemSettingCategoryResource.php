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

class SystemSettingCategoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = SystemSettingCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form|array
    {
        return SystemSettingCategoryForm::configure($form);
    }

    public static function table(Table $table): Table|array
    {
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
