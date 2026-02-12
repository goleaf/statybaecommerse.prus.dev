<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\Schemas\DiscountForm;
use App\Filament\Resources\DiscountResource\Schemas\DiscountInfolist;
use App\Models\Discount;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use UnitEnum;

final class DiscountResource extends BaseResource
{
    protected static ?string $model = Discount::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return __('admin.discounts.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.discounts.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.discounts.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return DiscountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DiscountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('messages.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'percentage' => __('admin.discounts.percentage'),
                        'fixed'      => __('admin.discounts.fixed_amount'),
                        default      => $state,
                    })
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('messages.value'))
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage'
                            ? $state . '%'
                            : '€' . number_format($state, 2)
                    )
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.discounts.is_active')),
                TextColumn::make('valid_from')
                    ->label(__('admin.discounts.valid_from'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label(__('admin.discounts.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('admin.discounts.no_expiry')),
                TextColumn::make('created_at')
                    ->label(__('admin.discounts.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        $relations = [];

        if (SchemaFacade::hasTable('discount_products')) {
            $relations[] = \App\Filament\Resources\DiscountResource\RelationManagers\ProductsRelationManager::class;
        }

        if (SchemaFacade::hasTable('discount_categories')) {
            $relations[] = \App\Filament\Resources\DiscountResource\RelationManagers\CategoriesRelationManager::class;
        }

        if (SchemaFacade::hasTable('discount_brands')) {
            $relations[] = \App\Filament\Resources\DiscountResource\RelationManagers\BrandsRelationManager::class;
        }

        if (SchemaFacade::hasTable('discount_collections')) {
            $relations[] = \App\Filament\Resources\DiscountResource\RelationManagers\CollectionsRelationManager::class;
        }

        if (SchemaFacade::hasTable('discount_customer_groups')) {
            $relations[] = \App\Filament\Resources\DiscountResource\RelationManagers\CustomerGroupsRelationManager::class;
        }

        if (SchemaFacade::hasTable('discount_redemptions')) {
            $relations[] = \App\Filament\Resources\DiscountResource\RelationManagers\RedemptionsRelationManager::class;
        }

        return $relations;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view'   => Pages\ViewDiscount::route('/{record}'),
            'edit'   => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
