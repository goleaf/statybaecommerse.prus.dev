<?php

namespace App\Filament\Resources\ReferralRewardLogs;


use Filament\Schemas\Schema;
use App\Filament\Resources\ReferralRewardLogs\Pages\CreateReferralRewardLog;
use App\Filament\Resources\ReferralRewardLogs\Pages\EditReferralRewardLog;
use App\Filament\Resources\ReferralRewardLogs\Pages\ListReferralRewardLogs;
use App\Filament\Resources\ReferralRewardLogs\Schemas\ReferralRewardLogForm;
use App\Filament\Resources\ReferralRewardLogs\Tables\ReferralRewardLogsTable;
use App\Models\ReferralRewardLog;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReferralRewardLogResource extends Resource
{
    protected static ?string $model = ReferralRewardLog::class;
    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema   
    {
        return ReferralRewardLogForm::configure($schema);
    }

    public static function table(Table $table): Table   
    {
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
            'index' => ListReferralRewardLogs::route('/'),
            'create' => CreateReferralRewardLog::route('/create'),
            'edit' => EditReferralRewardLog::route('/{record}/edit'),
        ];
    }
}
