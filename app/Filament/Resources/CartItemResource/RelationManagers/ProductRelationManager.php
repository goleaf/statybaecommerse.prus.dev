<?php declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final class ProductRelationManager extends RelationManager
{
    protected static string $relationship = 'product';

    protected static ?string $title = 'admin.cart_items.relations.product';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.products.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sku')
                    ->label(__('admin.products.fields.sku'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->label(__('admin.products.fields.price'))
                    ->numeric()
                    ->required()
                    ->prefix('€'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('admin.products.fields.image'))
                    ->getStateUsing(fn($record) => $record->getFirstMediaUrl('images', 'thumb'))
                    ->defaultImageUrl(asset('images/placeholder-product.png'))
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.products.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('admin.products.fields.sku'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('admin.common.copied'))
                    ->weight('mono'),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('admin.products.fields.price'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('admin.products.fields.is_visible'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.products.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('admin.products.filters.visible_only')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
