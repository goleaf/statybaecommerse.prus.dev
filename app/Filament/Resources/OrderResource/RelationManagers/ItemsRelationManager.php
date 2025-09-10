<?php declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'product_name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Item Information')
                    ->components([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('product_variant_id')
                            ->relationship('productVariant', 'sku')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\TextInput::make('unit_price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required(),
                        Forms\Components\TextInput::make('total_price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                Tables\Columns\ImageColumn::make('product.image')
                    ->defaultImageUrl('/images/placeholder-product.jpg')
                    ->circular(),
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('product_variant.sku')
                    ->label('SKU')
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Total'),
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
