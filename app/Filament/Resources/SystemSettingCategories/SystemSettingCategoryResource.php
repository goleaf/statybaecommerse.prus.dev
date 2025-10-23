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
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
class SystemSettingCategoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = SystemSettingCategory::class;

    /** @var string|\BackedEnum|null */
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        return SystemSettingCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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