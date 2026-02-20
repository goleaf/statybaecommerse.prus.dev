<?php

declare(strict_types=1);

namespace App\Filament\Resources\Referrals\RelationManagers;

use App\Filament\Resources\ReferralCodeStatistics\Schemas\ReferralCodeStatisticsForm;
use App\Filament\Resources\ReferralCodeStatistics\Tables\ReferralCodeStatisticsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ReferralCodeStatisticsRelationManager extends RelationManager
{
    protected static string $relationship = 'codeStatistics';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.referral_code_statistics');
    }

    public function form(Schema $schema): Schema
    {
        return ReferralCodeStatisticsForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ReferralCodeStatisticsTable::configure($table);
    }
}
