<?php

declare(strict_types=1);

namespace App\Filament\Resources\Referrals\RelationManagers;

use App\Filament\Resources\ReferralCodes\Schemas\ReferralCodeForm;
use App\Filament\Resources\ReferralCodes\Tables\ReferralCodesTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ReferralCodesRelationManager extends RelationManager
{
    protected static string $relationship = 'codes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.referral_codes');
    }

    public function form(Schema $schema): Schema
    {
        return ReferralCodeForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ReferralCodesTable::configure($table);
    }
}
