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
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
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
        // Configure the Filament resource form schema using the v4 Schema API.
        return ReferralCodeUsageLogForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
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