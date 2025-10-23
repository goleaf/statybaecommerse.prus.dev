<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatistics;
use App\Support\Concerns\HasNav;


use Filament\Schemas\Schema;
use App\Filament\Resources\ReferralStatistics\Pages\CreateReferralStatistics;
use App\Filament\Resources\ReferralStatistics\Pages\EditReferralStatistics;
use App\Filament\Resources\ReferralStatistics\Pages\ListReferralStatistics;
use App\Filament\Resources\ReferralStatistics\Schemas\ReferralStatisticsForm;
use App\Filament\Resources\ReferralStatistics\Tables\ReferralStatisticsTable;
use App\Models\ReferralStatistics;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReferralStatisticsResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema   
    {
        return ReferralStatisticsForm::configure($schema);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return ReferralStatisticsTable::configure($table);
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
            'index'  => ListReferralStatistics::route('/'),
            'create' => CreateReferralStatistics::route('/create'),
            'edit'   => EditReferralStatistics::route('/{record}/edit'),
        ];
    }
}