<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewards;

use App\Filament\Resources\ReferralRewards\Pages\CreateReferralReward;
use App\Filament\Resources\ReferralRewards\Pages\EditReferralReward;
use App\Filament\Resources\ReferralRewards\Pages\ListReferralRewards;
use App\Filament\Resources\ReferralRewards\Schemas\ReferralRewardForm;
use App\Filament\Resources\ReferralRewards\Tables\ReferralRewardsTable;
use App\Models\ReferralReward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReferralRewardResource extends Resource
{
    protected static ?string $model = ReferralReward::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return ReferralRewardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReferralRewardsTable::configure($table);
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
            'index'  => ListReferralRewards::route('/'),
            'create' => CreateReferralReward::route('/create'),
            'edit'   => EditReferralReward::route('/{record}/edit'),
        ];
    }
}
