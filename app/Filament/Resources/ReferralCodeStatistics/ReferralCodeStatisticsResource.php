<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatistics;

use App\Filament\Resources\ReferralCodeStatistics\Pages\CreateReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatistics\Pages\EditReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatistics\Pages\ListReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatistics\Schemas\ReferralCodeStatisticsForm;
use App\Filament\Resources\ReferralCodeStatistics\Tables\ReferralCodeStatisticsTable;
use App\Models\ReferralCodeStatistics;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ReferralCodeStatisticsResource extends Resource
{
    protected static ?string $model = ReferralCodeStatistics::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return ReferralCodeStatisticsForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        return ReferralCodeStatisticsTable::configure($table);
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
            'index'  => ListReferralCodeStatistics::route('/'),
            'create' => CreateReferralCodeStatistics::route('/create'),
            'edit'   => EditReferralCodeStatistics::route('/{record}/edit'),
        ];
    }
}