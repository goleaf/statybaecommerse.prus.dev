<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistories;

use App\Filament\Resources\SystemSettingHistories\Pages\CreateSystemSettingHistory;
use App\Filament\Resources\SystemSettingHistories\Pages\EditSystemSettingHistory;
use App\Filament\Resources\SystemSettingHistories\Pages\ListSystemSettingHistories;
use App\Filament\Resources\SystemSettingHistories\Schemas\SystemSettingHistoryForm;
use App\Filament\Resources\SystemSettingHistories\Tables\SystemSettingHistoriesTable;
use App\Models\SystemSettingHistory;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SystemSettingHistoryResource extends Resource
{
    use HasNav;

    /**
     * Ensure Filament resolves the canonical Eloquent model for the resource.
     */
    protected static ?string $model = SystemSettingHistory::class;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    /**
     * Promote a meaningful record title so breadcrumbs highlight the change reason.
     */
    protected static ?string $recordTitleAttribute = 'change_reason';

    public static function form(Schema $schema): Schema
    {
        return SystemSettingHistoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return SystemSettingHistoriesTable::configure($table);
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
            'index'  => ListSystemSettingHistories::route('/'),
            'create' => CreateSystemSettingHistory::route('/create'),
            'edit'   => EditSystemSettingHistory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        // Route the label through the translation files so the admin sidebar stays localised.
        return __('admin.system_setting_histories.navigation_label');
    }

    public static function getModelLabel(): string
    {
        // Using the shared translation key keeps the UI wording aligned with regression tests.
        return __('admin.system_setting_histories.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        // The plural label powers table headings and bulk action copy across the panel.
        return __('admin.system_setting_histories.plural_model_label');
    }
}
