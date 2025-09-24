<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Products';

    protected static ?string $modelLabel = 'Product';

    protected static ?string $pluralModelLabel = 'Products';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label(__('products.name'))
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', \Str::slug($state)) : null),
            TextInput::make('slug')
                ->label(__('products.slug'))
                ->unique(ignoreRecord: true)
                ->rules(['alpha_dash']),
            TextInput::make('sku')
                ->label(__('products.sku'))
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            TextInput::make('price')
                ->label(__('products.price'))
                ->numeric()
                ->prefix('€')
                ->required(),
            TextInput::make('sale_price')
                ->label(__('products.sale_price'))
                ->numeric()
                ->prefix('€'),
            TextInput::make('stock_quantity')
                ->label(__('products.stock_quantity'))
                ->numeric()
                ->default(0)
                ->minValue(0),
            Toggle::make('is_active')
                ->label(__('products.is_active'))
                ->default(true),
            Toggle::make('is_featured')
                ->label(__('products.is_featured')),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('products.image'))
                    ->circular()
                    ->size(40),
                TextColumn::make('name')
                    ->label(__('products.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('sku')
                    ->label(__('products.sku'))
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')
                    ->label(__('products.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label(__('products.sale_price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock_quantity')
                    ->label(__('products.stock_quantity'))
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),
                IconColumn::make('is_active')
                    ->label(__('products.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_featured')
                    ->label(__('products.is_featured'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label(__('products.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('products.status'))
                    ->options([
                        '1' => __('products.active'),
                        '0' => __('products.inactive'),
                    ]),
                TernaryFilter::make('is_featured')
                    ->trueLabel(__('products.featured_only'))
                    ->falseLabel(__('products.not_featured'))
                    ->native(false),
                SelectFilter::make('stock_status')
                    ->label(__('products.stock_status'))
                    ->options([
                        'in_stock' => __('products.in_stock'),
                        'low_stock' => __('products.low_stock'),
                        'out_of_stock' => __('products.out_of_stock'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'in_stock' => $query->where('stock_quantity', '>', 10),
                            'low_stock' => $query->whereBetween('stock_quantity', [1, 10]),
                            'out_of_stock' => $query->where('stock_quantity', 0),
                            default => $query,
                        };
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('sort_order')
                            ->label(__('products.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DetachAction::make(),
            ])
            ->bulkActions([
                // Add bulk actions if needed
            ])
            ->defaultSort('name');
    }
}
