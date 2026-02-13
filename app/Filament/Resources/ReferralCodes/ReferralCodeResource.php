<?php

namespace App\Filament\Resources\ReferralCodes;

use App\Filament\Resources\ReferralCodes\Pages\CreateReferralCode;
use App\Filament\Resources\ReferralCodes\Pages\EditReferralCode;
use App\Filament\Resources\ReferralCodes\Pages\ListReferralCodes;
use App\Filament\Resources\ReferralCodes\Schemas\ReferralCodeForm;
use App\Filament\Resources\ReferralCodes\Tables\ReferralCodesTable;
use App\Models\ReferralCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReferralCodeResource extends Resource
{
    protected static ?string $model = ReferralCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReferralCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReferralCodesTable::configure($table);
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
            'index' => ListReferralCodes::route('/'),
            'create' => CreateReferralCode::route('/create'),
            'edit' => EditReferralCode::route('/{record}/edit'),
        ];
    }
}
