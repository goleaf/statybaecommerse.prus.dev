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

use Filament\Schemas\Schema;
class ReferralRewardLogResource extends Resource
{
    use HasNav;

    protected static ?string $model = ReferralRewardLog::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReferralRewardLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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