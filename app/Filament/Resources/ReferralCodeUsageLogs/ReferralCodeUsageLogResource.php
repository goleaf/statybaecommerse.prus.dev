<?php

namespace App\Filament\Resources\ReferralCodeUsageLogs;
use App\Filament\Resources\ReferralCodeUsageLogs\Pages\CreateReferralCodeUsageLog;
use App\Filament\Resources\ReferralCodeUsageLogs\Pages\EditReferralCodeUsageLog;
use App\Filament\Resources\ReferralCodeUsageLogs\Pages\ListReferralCodeUsageLogs;
use App\Filament\Resources\ReferralCodeUsageLogs\Schemas\ReferralCodeUsageLogForm;
use App\Filament\Resources\ReferralCodeUsageLogs\Tables\ReferralCodeUsageLogsTable;
use App\Models\ReferralCodeUsageLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
class ReferralCodeUsageLogResource extends Resource
{
    protected static ?string $model = ReferralCodeUsageLog::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function form(Schema $schema): Schema
    {
        return ReferralCodeUsageLogForm::configure($schema);
    }
    public static function table(Table $table): Table
        return ReferralCodeUsageLogsTable::configure($table);
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => ListReferralCodeUsageLogs::route('/'),
            'create' => CreateReferralCodeUsageLog::route('/create'),
            'edit' => EditReferralCodeUsageLog::route('/{record}/edit'),
}
