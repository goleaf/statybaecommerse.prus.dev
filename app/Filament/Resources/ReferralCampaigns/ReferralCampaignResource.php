<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCampaigns;

use App\Filament\Resources\ReferralCampaigns\Pages\CreateReferralCampaign;
use App\Filament\Resources\ReferralCampaigns\Pages\EditReferralCampaign;
use App\Filament\Resources\ReferralCampaigns\Pages\ListReferralCampaigns;
use App\Filament\Resources\ReferralCampaigns\Schemas\ReferralCampaignForm;
use App\Filament\Resources\ReferralCampaigns\Tables\ReferralCampaignsTable;
use App\Models\ReferralCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReferralCampaignResource extends Resource
{
    protected static ?string $model = ReferralCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return ReferralCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReferralCampaignsTable::configure($table);
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
            'index'  => ListReferralCampaigns::route('/'),
            'create' => CreateReferralCampaign::route('/create'),
            'edit'   => EditReferralCampaign::route('/{record}/edit'),
        ];
    }
}
