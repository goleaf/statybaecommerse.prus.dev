<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistories;

use App\Filament\Resources\SystemSettingHistories\Pages\CreateSystemSettingHistory;
use App\Filament\Resources\SystemSettingHistories\Pages\EditSystemSettingHistory;
use App\Filament\Resources\SystemSettingHistories\Pages\ListSystemSettingHistories;
use App\Filament\Resources\SystemSettingHistories\Schemas\SystemSettingHistoryForm;
use App\Filament\Resources\SystemSettingHistories\Tables\SystemSettingHistoriesTable;
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
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
}
