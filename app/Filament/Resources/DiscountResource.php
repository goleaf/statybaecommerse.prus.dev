<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

final class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Discounts';
    }

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('discounts.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('discounts.plural');
    }

    public static function getModelLabel(): string
    {
        return __('discounts.single');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view' => Pages\ViewDiscount::route('/{record}'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
}
