<?php declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Tables\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions as Actions;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

final class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->maxLength(255)
                    ->unique('products', 'sku', ignoreRecord: true),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'archived' => 'Archived',
                    ])
                    ->default('draft')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->defaultImageUrl('/images/placeholder-product.jpg')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('price')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable()
                    ->color(fn($state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'warning',
                        'inactive' => 'danger',
                        'archived' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\Filter::make('visible')
                    ->query(fn(Builder $query): Builder => $query->where('is_visible', true)),
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn(Builder $query): Builder => $query->where('stock_quantity', '<=', 10)),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
