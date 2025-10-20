<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatistics;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\ReferralCodeStatistics\Pages\CreateReferralCodeStatistics;
use UnitEnum;
use BackedEnum;
use App\Filament\Resources\ReferralCodeStatistics\Pages\EditReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatistics\Pages\ListReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatistics\Schemas\ReferralCodeStatisticsForm;
use App\Filament\Resources\ReferralCodeStatistics\Tables\ReferralCodeStatisticsTable;
use App\Models\ReferralCodeStatistics;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Filament\Forms\Form;

class ReferralCodeStatisticsResource extends Resource
{
    use HasNav;

    protected static ?string $model = ReferralCodeStatistics::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        return ReferralCodeStatisticsForm::configure($form);
    }

    public static function table(Table $table): Table
    {
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
