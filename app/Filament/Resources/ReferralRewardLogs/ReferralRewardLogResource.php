<?php

namespace App\Filament\Resources\ReferralRewardLogs;

use App\Filament\Resources\ReferralRewardLogs\Pages\CreateReferralRewardLog;
use UnitEnum;
use BackedEnum;
use App\Filament\Resources\ReferralRewardLogs\Pages\EditReferralRewardLog;
use App\Filament\Resources\ReferralRewardLogs\Pages\ListReferralRewardLogs;
use App\Filament\Resources\ReferralRewardLogs\Schemas\ReferralRewardLogForm;
use App\Filament\Resources\ReferralRewardLogs\Tables\ReferralRewardLogsTable;
use App\Models\ReferralRewardLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReferralRewardLogResource extends Resource
{
    protected static ?string $model = ReferralRewardLog::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReferralRewardLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReferralRewardLogsTable::configure($table);
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
            'index' => ListReferralRewardLogs::route('/'),
            'create' => CreateReferralRewardLog::route('/create'),
            'edit' => EditReferralRewardLog::route('/{record}/edit'),
        ];
    }
}
