<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use App\Models\ProductSimilarity;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SimilaritiesRelationManager extends RelationManager
{
    protected static string $relationship = 'similarities';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.similarities');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                $this->makeSimilarProductSelect(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['similarProduct.primaryImage']))
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('main_image')
                    ->label(__('messages.image'))
                    ->disk('public')
                    ->getStateUsing(static fn ($record): ?string => $record->similarProduct?->primaryImage?->path)
                    ->circular(),
                TextColumn::make('similarProduct.name')
                    ->label(__('messages.similar_product'))
                    ->description(fn ($record) => $record->similarProduct?->sku)
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('assign_existing_product')
                    ->label(__('admin.labels.assign_existing_product'))
                    ->icon('heroicon-o-plus')
                    ->form([
                        $this->makeSimilarProductSelect(),
                    ])
                    ->action(function (array $data): void {
                        ProductSimilarity::query()->firstOrCreate([
                            'product_id'         => $this->getOwnerRecord()->getKey(),
                            'similar_product_id' => (int) $data['similar_product_id'],
                        ]);
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function makeSimilarProductSelect(): Select
    {
        $ownerId = (int) $this->getOwnerRecord()->getKey();

        return Select::make('similar_product_id')
            ->label(__('messages.similar_product'))
            ->columnSpanFull()
            ->required()
            ->searchable()
            ->preload()
            ->options(function () use ($ownerId): array {
                $existingIds = ProductSimilarity::query()
                    ->where('product_id', $ownerId)
                    ->pluck('similar_product_id');

                return Product::query()
                    ->withoutGlobalScopes()
                    ->whereKeyNot($ownerId)
                    ->whereNotIn('id', $existingIds)
                    ->orderBy('name')
                    ->limit(500)
                    ->get()
                    ->mapWithKeys(static fn (Product $product): array => [
                        (string) $product->getKey() => trim(($product->name ?? '') . ' [' . ($product->sku ?? '-') . ']'),
                    ])
                    ->all();
            })
            ->getOptionLabelUsing(static function ($value): ?string {
                $product = Product::query()->withoutGlobalScopes()->find($value);

                if (! $product instanceof Product) {
                    return null;
                }

                return trim(($product->name ?? '') . ' [' . ($product->sku ?? '-') . ']');
            });
    }
}
