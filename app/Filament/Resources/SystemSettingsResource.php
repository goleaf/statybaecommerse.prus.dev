<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingsResource\Pages;
use App\Filament\Resources\SystemSettingsResource\Schemas\SystemSettingsForm;
use App\Filament\Resources\SystemSettingsResource\Tables\SystemSettingsTable;
use App\Models\SystemSetting;
use App\Support\Nav;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SystemSettingsResource extends BaseResource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationGroup(): ?string
    {
        return Nav::groupForResource(static::class);
    }

    public static function getNavigationSort(): ?int
    {
        return Nav::sortForResource(static::class);
    }

    public static function getRecordTitleAttribute(): ?string
    {
        return 'key';
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.system_settings');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.system_settings');
    }

    public static function getModelLabel(): string
    {
        return __('messages.system_settings');
    }

    public static function form(Schema $schema): Schema
    {
        return SystemSettingsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SystemSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'view'   => Pages\ViewSystemSetting::route('/{record}'),
            'edit'   => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
