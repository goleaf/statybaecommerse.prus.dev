<?php

declare(strict_types=1);

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
use Illuminate\Contracts\Support\Htmlable;

class ReferralCodeUsageLogResource extends Resource
{
    protected static ?string $model = ReferralCodeUsageLog::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return Heroicon::OutlinedRectangleStack;
    }

    public static function form(Schema $schema): Schema
    {
        return ReferralCodeUsageLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReferralCodeUsageLogsTable::configure($table);
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
            'index' => ListReferralCodeUsageLogs::route('/'),
            'create' => CreateReferralCodeUsageLog::route('/create'),
            'edit' => EditReferralCodeUsageLog::route('/{record}/edit'),
        ];
    }
}
