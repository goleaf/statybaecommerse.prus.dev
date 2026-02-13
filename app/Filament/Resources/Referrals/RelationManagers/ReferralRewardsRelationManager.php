<?php

declare(strict_types=1);

namespace App\Filament\Resources\Referrals\RelationManagers;

use App\Filament\Resources\ReferralRewards\Schemas\ReferralRewardForm;
use App\Filament\Resources\ReferralRewards\Tables\ReferralRewardsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ReferralRewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewards';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Referral Rewards';
    }

    public function form(Schema $schema): Schema
    {
        return ReferralRewardForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ReferralRewardsTable::configure($table)
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
