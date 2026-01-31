<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductRequestResource\Pages;
use App\Filament\Resources\ProductRequestResource\Schemas\ProductRequestForm;
use App\Filament\Resources\ProductRequestResource\Tables\ProductRequestsTable;
use App\Models\ProductRequest;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class ProductRequestResource extends BaseResource
{
    protected static ?string $model = ProductRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::pending()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return self::getModel()::pending()->count() > 0 ? 'warning' : 'gray';
    }

    public static function form(Schema $schema): Schema
    {
        return ProductRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductRequests::route('/'),
            'create' => Pages\CreateProductRequest::route('/create'),
            'edit'   => Pages\EditProductRequest::route('/{record}/edit'),
        ];
    }
}
