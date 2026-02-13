<?php

declare(strict_types=1);

namespace App\Filament\Resources\Referrals\RelationManagers;

use App\Filament\Resources\ReferralRewardLogs\Schemas\ReferralRewardLogForm;
use App\Filament\Resources\ReferralRewardLogs\Tables\ReferralRewardLogsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ReferralRewardLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewardLogs';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Referral Reward Logs';
    }

    public function form(Schema $schema): Schema
    {
        return ReferralRewardLogForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ReferralRewardLogsTable::configure($table);
    }
}
