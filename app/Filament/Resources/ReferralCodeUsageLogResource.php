<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCodeUsageLogResource\Pages;
use App\Filament\Resources\ReferralCodeUsageLogs\Schemas\ReferralCodeUsageLogForm as ReferralCodeUsageLogFormSchema;
use App\Filament\Resources\ReferralCodeUsageLogs\Tables\ReferralCodeUsageLogsTable as ReferralCodeUsageLogsTableSchema;
use App\Models\ReferralCodeUsageLog;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Schemas\Schema;

use App\Filament\Resources\ReferralCodeUsageLogResource\Pages;
use App\Models\ReferralCodeUsageLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use UnitEnum;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
/**
 * ReferralCodeUsageLogResource
 *
 * Filament v4 resource for ReferralCodeUsageLog management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ReferralCodeUsageLogResource extends Resource
{
    use HasNav;

    protected static ?string $model = ReferralCodeUsageLog::class;

    protected static ?int $navigationSort = 18;

    protected static ?string $recordTitleAttribute = 'ip_address';

    protected static string|\UnitEnum|null $navigationGroup = 'Analytics';

    public static function getNavigationLabel(): string
    {
        return __('admin.referral_code_usage_logs.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.referral_code_usage_logs.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.referral_code_usage_logs.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ReferralCodeUsageLogFormSchema::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return ReferralCodeUsageLogsTableSchema::configure($table);
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
            'index'  => Pages\ListReferralCodeUsageLogs::route('/'),
            'create' => Pages\CreateReferralCodeUsageLog::route('/create'),
            'view'   => Pages\ViewReferralCodeUsageLog::route('/{record}'),
            'edit'   => Pages\EditReferralCodeUsageLog::route('/{record}/edit'),
        ];
    }
}