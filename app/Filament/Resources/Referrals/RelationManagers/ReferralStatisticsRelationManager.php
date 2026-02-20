<?php

declare(strict_types=1);

namespace App\Filament\Resources\Referrals\RelationManagers;

use App\Filament\Resources\ReferralStatistics\Schemas\ReferralStatisticsForm;
use App\Filament\Resources\ReferralStatistics\Tables\ReferralStatisticsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ReferralStatisticsRelationManager extends RelationManager
{
    protected static string $relationship = 'statistics';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.referral_statistics');
    }

    public function form(Schema $schema): Schema
    {
        return ReferralStatisticsForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ReferralStatisticsTable::configure($table)
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
