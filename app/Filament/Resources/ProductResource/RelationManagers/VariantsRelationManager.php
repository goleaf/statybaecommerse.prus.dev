<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\RelationManagers\Concerns\ResolvesOwnerPageRedirect;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductVariantResource;
use App\Filament\Resources\ProductVariantResource\RelationManagers\AttributesRelationManager;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VariantsRelationManager extends RelationManager
{
    use ResolvesOwnerPageRedirect;

    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.variants');
    }

    public function table(Table $table): Table
    {
        $ownerMainImageUrl = $this->resolveOwnerMainImageUrl();

        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query
                ->withoutGlobalScopes()
                ->with(['attributes.attribute']))
            ->recordUrl(fn (ProductVariant $record): string => ProductVariantResource::getUrl('edit', [
                'record'   => $record,
                'redirect' => $this->resolveOwnerPageRedirectUrl(ProductResource::class),
            ]))
            ->columns([
                SpatieMediaLibraryImageColumn::make('media')
                    ->label(__('messages.image'))
                    ->collection('images')
                    ->defaultImageUrl($ownerMainImageUrl)
                    ->limit(1)
                    ->square(),
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('attribute_summary')
                    ->label(__('messages.attributes'))
                    ->state(fn (ProductVariant $record): string => self::formatAttributeSummary($record))
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(__('messages.stock_quantity'))
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->label(__('messages.available_quantity'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(static function (mixed $state): string {
                        $available = is_numeric($state) ? (int) $state : 0;

                        return match (true) {
                            $available <= 0 => 'danger',
                            $available <= 5 => 'warning',
                            default         => 'success',
                        };
                    }),
                ToggleColumn::make('is_enabled')
                    ->sortable()
                    ->label(__('messages.enabled')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('create')
                    ->icon('heroicon-m-plus')
                    ->url(fn (): string => ProductVariantResource::getUrl('create', [
                        'product_id' => $this->getOwnerRecord()->getKey(),
                        'redirect'   => $this->resolveOwnerPageRedirectUrl(ProductResource::class),
                    ])),
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['sku', 'name'])
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query
                        ->withoutGlobalScopes()
                        ->whereDoesntHave('products', fn (Builder $productsQuery): Builder => $productsQuery
                            ->whereKey($this->getOwnerRecord()->getKey())))
                    ->using(function (AttachAction $action, BelongsToMany $relationship): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Model) {
                            return;
                        }

                        // Avoid duplicate pivot insert failures when a variant is re-attached.
                        $relationship->syncWithoutDetaching([$record->getKey()]);
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (ProductVariant $record): string => ProductVariantResource::getUrl('view', [
                        'record'   => $record,
                        'redirect' => $this->resolveOwnerPageRedirectUrl(ProductResource::class),
                    ])),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (ProductVariant $record): string => ProductVariantResource::getUrl('edit', [
                        'record'   => $record,
                        'redirect' => $this->resolveOwnerPageRedirectUrl(ProductResource::class),
                    ])),
                Action::make('attributes')
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->label(__('messages.attributes'))
                    ->url(fn (ProductVariant $record): string => $this->getAttributesUrl($record)),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function resolveOwnerMainImageUrl(): ?string
    {
        $ownerRecord = $this->getOwnerRecord();

        if (! $ownerRecord instanceof Product) {
            return null;
        }

        return $ownerRecord->getMainImage('thumb') ?? $ownerRecord->getMainImage();
    }

    private function getAttributesUrl(ProductVariant $record): string
    {
        $parameters = [
            'record'   => $record,
            'redirect' => $this->resolveOwnerPageRedirectUrl(ProductResource::class),
        ];

        $relationTabKey = self::resolveAttributesRelationTabKey();

        if ($relationTabKey !== null) {
            $parameters['relation'] = $relationTabKey;
        }

        return ProductVariantResource::getUrl('edit', $parameters);
    }

    private static function resolveAttributesRelationTabKey(): ?string
    {
        $relationKey = array_search(AttributesRelationManager::class, ProductVariantResource::getRelations(), true);

        if ($relationKey === false) {
            return null;
        }

        return (string) $relationKey;
    }

    private static function formatAttributeSummary(ProductVariant $record): string
    {
        $attributes = $record->getVariantAttributes();

        if ($attributes === []) {
            return '';
        }

        $pairs = [];

        foreach ($attributes as $attributeName => $attributeValue) {
            $normalizedName = trim((string) $attributeName);
            $normalizedValue = trim((string) $attributeValue);

            if ($normalizedName === '' || $normalizedValue === '') {
                continue;
            }

            $pairs[] = ucfirst($normalizedName) . ': ' . $normalizedValue;
        }

        return implode(', ', $pairs);
    }
}
