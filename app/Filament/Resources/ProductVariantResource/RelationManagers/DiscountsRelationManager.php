<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use App\Filament\Resources\DiscountResource;
use App\Models\Discount;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema as SchemaFacade;

class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.discounts.plural_model_label');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return SchemaFacade::hasTable('discounts') && SchemaFacade::hasTable('discount_products');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Discount $record): string => DiscountResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('code')
                    ->label(__('messages.code'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('value')
                    ->label(__('messages.value'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Discount $record): string => DiscountResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
