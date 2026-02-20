<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingCategoryResource\Pages;
use App\Filament\Resources\SystemSettingCategoryResource\Schemas\SystemSettingCategoryForm;
use App\Filament\Resources\SystemSettingCategoryResource\Tables\SystemSettingCategoriesTable;
use App\Models\Scopes\ActiveScope;
use App\Models\SystemSettingCategory;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SystemSettingCategoryResource extends BaseResource
{
    protected static ?string $model = SystemSettingCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('admin.navigation.settings');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationItems(): array
    {
        return [];
    }

    public static function getRecordTitleAttribute(): ?string
    {
        return 'name';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.system_setting_categories.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.system_setting_categories.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.system_setting_categories.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return SystemSettingCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SystemSettingCategoriesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSystemSettingCategories::route('/'),
            'create' => Pages\CreateSystemSettingCategory::route('/create'),
            'edit'   => Pages\EditSystemSettingCategory::route('/{record}/edit'),
        ];
    }
}
