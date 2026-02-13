<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountRedemptionResource\Pages\CreateDiscountRedemption;
use App\Filament\Resources\DiscountRedemptionResource\Pages\EditDiscountRedemption;
use App\Filament\Resources\DiscountRedemptionResource\Pages\ListDiscountRedemptions;
use App\Filament\Resources\DiscountRedemptionResource\Pages\ViewDiscountRedemption;
use App\Models\DiscountRedemption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class DiscountRedemptionResource extends Resource
{
    protected static ?string $model = DiscountRedemption::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|UnitEnum|null $navigationGroup = 'Discounts';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDiscountRedemptions::route('/'),
            'create' => CreateDiscountRedemption::route('/create'),
            'view'   => ViewDiscountRedemption::route('/{record}'),
            'edit'   => EditDiscountRedemption::route('/{record}/edit'),
        ];
    }
}
