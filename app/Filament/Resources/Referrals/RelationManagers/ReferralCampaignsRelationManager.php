<?php

declare(strict_types=1);

namespace App\Filament\Resources\Referrals\RelationManagers;

use App\Filament\Resources\ReferralCampaigns\Schemas\ReferralCampaignForm;
use App\Filament\Resources\ReferralCampaigns\Tables\ReferralCampaignsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ReferralCampaignsRelationManager extends RelationManager
{
    protected static string $relationship = 'campaigns';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Referral Campaigns';
    }

    public function form(Schema $schema): Schema
    {
        return ReferralCampaignForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ReferralCampaignsTable::configure($table);
    }
}
