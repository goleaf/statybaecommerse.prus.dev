<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatistics;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\ReferralStatistics\Pages\CreateReferralStatistics;
use App\Filament\Resources\ReferralStatistics\Pages\EditReferralStatistics;
use App\Filament\Resources\ReferralStatistics\Pages\ListReferralStatistics;
use App\Filament\Resources\ReferralStatistics\Schemas\ReferralStatisticsForm;
use App\Filament\Resources\ReferralStatistics\Tables\ReferralStatisticsTable;
use App\Models\ReferralStatistics;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ReferralStatisticsResource extends Resource
{
    use HasNav;

    protected static ?string $model = ReferralStatistics::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return ReferralStatisticsForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
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
