<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardLogs;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\ReferralRewardLogs\Pages\CreateReferralRewardLog;
use App\Filament\Resources\ReferralRewardLogs\Pages\EditReferralRewardLog;
use App\Filament\Resources\ReferralRewardLogs\Pages\ListReferralRewardLogs;
use App\Filament\Resources\ReferralRewardLogs\Schemas\ReferralRewardLogForm;
use App\Filament\Resources\ReferralRewardLogs\Tables\ReferralRewardLogsTable;
use App\Models\ReferralRewardLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ReferralRewardLogResource extends Resource
{
    use HasNav;

    protected static ?string $model = ReferralRewardLog::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {

        $form = $schema; // Preserve legacy variable naming for existing schema definitions.

        return ReferralRewardLogForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return ReferralRewardLogsTable::configure($table);
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
            'index'  => ListReferralRewardLogs::route('/'),
            'create' => CreateReferralRewardLog::route('/create'),
            'edit'   => EditReferralRewardLog::route('/{record}/edit'),
        ];
    }
}
