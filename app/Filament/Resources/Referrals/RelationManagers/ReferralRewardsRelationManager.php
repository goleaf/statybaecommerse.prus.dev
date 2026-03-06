<?php

declare(strict_types=1);

namespace App\Filament\Resources\Referrals\RelationManagers;

use App\Filament\Resources\ReferralRewards\Schemas\ReferralRewardForm;
use App\Filament\Resources\ReferralRewards\Tables\ReferralRewardsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ReferralRewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewards';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.referral_rewards');
    }

    public function form(Schema $schema): Schema
    {
        return ReferralRewardForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ReferralRewardsTable::configure($table)
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'referral_id' => $this->getOwnerRecord()->getKey(),
                    ]),
            ]);
    }
}
