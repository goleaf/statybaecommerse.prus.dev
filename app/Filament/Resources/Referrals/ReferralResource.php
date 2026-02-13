<?php

declare(strict_types=1);

namespace App\Filament\Resources\Referrals;

use App\Filament\Resources\Referrals\Pages\CreateReferral;
use App\Filament\Resources\Referrals\Pages\EditReferral;
use App\Filament\Resources\Referrals\Pages\ListReferrals;
use App\Filament\Resources\Referrals\RelationManagers\ReferralCampaignsRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralCodesRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralCodeStatisticsRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralCodeUsageLogsRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralRewardLogsRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralRewardsRelationManager;
use App\Filament\Resources\Referrals\RelationManagers\ReferralStatisticsRelationManager;
use App\Filament\Resources\Referrals\Schemas\ReferralForm;
use App\Filament\Resources\Referrals\Tables\ReferralsTable;
use App\Models\Referral;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationLabel(): string
    {
        return 'Referrals';
    }

    public static function form(Schema $schema): Schema
    {
        return ReferralForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReferralsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ReferralCampaignsRelationManager::class,
            ReferralCodesRelationManager::class,
            ReferralCodeStatisticsRelationManager::class,
            ReferralCodeUsageLogsRelationManager::class,
            ReferralRewardsRelationManager::class,
            ReferralRewardLogsRelationManager::class,
            ReferralStatisticsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListReferrals::route('/'),
            'create' => CreateReferral::route('/create'),
            'edit'   => EditReferral::route('/{record}/edit'),
        ];
    }
}
