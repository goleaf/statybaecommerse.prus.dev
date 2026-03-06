<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarityResource\RelationManagers;

use App\Filament\Concerns\ResolvesVariantImageUrl;
use App\Filament\Resources\ProductVariantResource;
use App\Models\ProductVariant;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SimilarProductVariantsRelationManager extends RelationManager
{
    use ResolvesVariantImageUrl;

    protected static string $relationship = 'similarVariants';

    protected static ?string $recordTitleAttribute = 'sku';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.products.similar_products') . ' ' . __('admin.navigation.product_variants');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query
                ->with(['media', 'product.primaryImage']))
            ->recordUrl(fn (ProductVariant $record): string => ProductVariantResource::getUrl('edit', ['record' => $record]))
            ->columns([
                ImageColumn::make('variant_image')
                    ->label(__('messages.image'))
                    ->disk('public')
                    ->getStateUsing(static fn (ProductVariant $record): ?string => self::resolveVariantImageUrl($record))
                    ->circular(),
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (ProductVariant $record): string => ProductVariantResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
