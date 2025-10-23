<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatistics;

use App\Filament\Resources\ReferralCodeStatistics\Pages\CreateReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatistics\Pages\EditReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatistics\Pages\ListReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatistics\Schemas\ReferralCodeStatisticsForm;
use App\Filament\Resources\ReferralCodeStatistics\Tables\ReferralCodeStatisticsTable;
use App\Models\ReferralCodeStatistics;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return ReferralCodeStatisticsForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
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
            'index' => ListReferralCodeStatistics::route('/'),
            'create' => CreateReferralCodeStatistics::route('/create'),
            'edit' => EditReferralCodeStatistics::route('/{record}/edit'),
        ];
    }
}
