<?php

declare(strict_types=1);

namespace App\Filament\Resources\Referrals\RelationManagers;

use App\Filament\Resources\ReferralCodeUsageLogs\Schemas\ReferralCodeUsageLogForm;
use App\Filament\Resources\ReferralCodeUsageLogs\Tables\ReferralCodeUsageLogsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ReferralCodeUsageLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'codeUsageLogs';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Referral Code Usage Logs';
    }

    public function form(Schema $schema): Schema
    {
        return ReferralCodeUsageLogForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ReferralCodeUsageLogsTable::configure($table);
    }
}
