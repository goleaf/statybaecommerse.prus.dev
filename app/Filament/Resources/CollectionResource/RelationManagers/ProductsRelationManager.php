<?php declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use App\Models\Product;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Collection Products';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label(__('admin.collections.fields.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('admin.collections.fields.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Forms\Components\Toggle::make('is_featured')
                    ->label(__('admin.collections.fields.is_featured'))
                    ->default(false),

                Forms\Components\DateTimePicker::make('featured_until')
                    ->label(__('admin.collections.fields.featured_until'))
                    ->visible(fn(Forms\Get $get): bool => $get('is_featured')),

                Forms\Components\Textarea::make('notes')
                    ->label(__('admin.collections.fields.notes'))
                    ->rows(2)
                    ->maxLength(500),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('admin.products.fields.image'))
                    ->getStateUsing(fn(Product $record) => $record->getFirstMediaUrl('images', 'thumb'))
                    ->defaultImageUrl(asset('images/placeholder-product.png'))
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.products.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('sku')
                    ->label(__('admin.products.fields.sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('admin.copied'))
                    ->weight('mono'),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label(__('admin.products.fields.brand'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('admin.products.fields.price'))
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('admin.products.fields.stock_quantity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('admin.products.fields.is_published'))
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.collections.fields.is_featured'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.collections.fields.sort_order'))
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.products.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label(__('admin.products.filters.is_published'))
                    ->placeholder(__('admin.products.status.published'))
                    ->trueLabel(__('admin.products.status.published'))
                    ->falseLabel(__('admin.products.status.draft')),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('admin.collections.filters.is_featured'))
                    ->placeholder(__('admin.collections.status.featured'))
                    ->trueLabel(__('admin.collections.status.featured'))
                    ->falseLabel(__('admin.collections.status.not_featured')),

                Tables\Filters\SelectFilter::make('brand_id')
                    ->label(__('admin.products.filters.brand'))
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('admin.collections.actions.add_product'))
                    ->preloadRecordSelect()
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label(__('admin.collections.fields.product'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('admin.collections.fields.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\Toggle::make('is_featured')
                            ->label(__('admin.collections.fields.is_featured'))
                            ->default(false),

                        Forms\Components\DateTimePicker::make('featured_until')
                            ->label(__('admin.collections.fields.featured_until'))
                            ->visible(fn(Forms\Get $get): bool => $get('is_featured')),

                        Forms\Components\Textarea::make('notes')
                            ->label(__('admin.collections.fields.notes'))
                            ->rows(2)
                            ->maxLength(500),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('admin.collections.actions.edit_product')),

                Tables\Actions\DetachAction::make()
                    ->label(__('admin.collections.actions.remove_product'))
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.collections.confirmations.remove_product')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label(__('admin.collections.actions.remove_products'))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.collections.confirmations.remove_products')),

                    Tables\Actions\BulkAction::make('toggle_featured')
                        ->label(__('admin.collections.actions.toggle_featured'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->pivot->update(['is_featured' => !$record->pivot->is_featured]);
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.collections.confirmations.toggle_featured')),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}