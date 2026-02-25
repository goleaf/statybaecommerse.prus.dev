<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use App\Filament\Resources\ProductResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SimilaritiesRelationManager extends RelationManager
{
    protected static string $relationship = 'similarities';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.products.similar_products');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Model $record): string => ProductResource::getUrl('edit', ['record' => $record->similar_product_id]))
            ->columns([
                TextColumn::make('similarProduct.name')
                    ->label(__('messages.product'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('similarProduct.sku')
                    ->label(__('messages.sku'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Model $record): string => ProductResource::getUrl('edit', ['record' => $record->similar_product_id])),
            ]);
    }
}
