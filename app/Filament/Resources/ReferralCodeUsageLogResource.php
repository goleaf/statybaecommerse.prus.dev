<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\ReferralCodeUsageLogResource\Pages;
use App\Models\ReferralCodeUsageLog;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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

    /**
     * Navigation group for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationGroup = 'Analytics';

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

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return ReferralCodeUsageLogFormSchema::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
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
