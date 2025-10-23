<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeUsageLogs;
use App\Support\Concerns\HasNav;

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
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class ReferralCodeUsageLogResource extends Resource
{
    use HasNav;

    protected static ?string $model = ReferralCodeUsageLog::class;

    public static function getNavigationIcon(): BackedEnum|\UnitEnum|Htmlable|string|null
    {
        return Heroicon::OutlinedRectangleStack;
    }

    public static function form(Schema $schema): Schema
    {
        return ReferralCodeUsageLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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
            'index'  => ListReferralCodeUsageLogs::route('/'),
            'create' => CreateReferralCodeUsageLog::route('/create'),
            'edit'   => EditReferralCodeUsageLog::route('/{record}/edit'),
        ];
    }
}