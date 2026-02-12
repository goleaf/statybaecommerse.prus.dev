<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use App\Models\Product;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\VisibleScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'product';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.products.plural_model_label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn ($query) => $query
                ->withoutGlobalScopes([
                    ActiveScope::class,
                    PublishedScope::class,
                    VisibleScope::class,
                ])
                ->with(['primaryImage', 'brand']))
            ->recordTitleAttribute('name')
            ->paginated(false)
            ->columns([
                ImageColumn::make('main_image')
                    ->label(__('messages.image'))
                    ->disk('public')
                    ->getStateUsing(static fn (Product $record): ?string => $record->primaryImage?->path)
                    ->circular(),
                TextColumn::make('name')
                    ->sortable()
                    ->label(__('messages.name')),
                TextColumn::make('sku')
                    ->sortable()
                    ->label(__('messages.sku')),
                TextColumn::make('price')
                    ->sortable()
                    ->label(__('messages.price'))
                    ->money('EUR'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
