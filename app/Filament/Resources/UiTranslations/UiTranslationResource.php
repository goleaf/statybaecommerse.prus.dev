<?php

declare(strict_types=1);

namespace App\Filament\Resources\UiTranslations;

use App\Filament\Resources\UiTranslations\Pages\CreateUiTranslation;
use App\Filament\Resources\UiTranslations\Pages\EditUiTranslation;
use App\Filament\Resources\UiTranslations\Pages\ListUiTranslations;
use App\Filament\Resources\UiTranslations\Schemas\UiTranslationForm;
use App\Filament\Resources\UiTranslations\Tables\UiTranslationsTable;
use App\Models\UiTranslation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UiTranslationResource extends Resource
{
    protected static ?string $model = UiTranslation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    public static function getModelLabel(): string
    {
        return __('admin.system_settings.normal_setting_translations.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.system_settings.normal_setting_translations.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.system_settings.normal_setting_translations.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return UiTranslationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UiTranslationsTable::configure($table);
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
            'index'  => ListUiTranslations::route('/'),
            'create' => CreateUiTranslation::route('/create'),
            'edit'   => EditUiTranslation::route('/{record}/edit'),
        ];
    }
}
