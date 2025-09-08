<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Tables\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

final class CollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'collections';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('collection_id')
                    ->relationship('collections', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->defaultImageUrl('/images/placeholder-collection.jpg')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'manual' => 'primary',
                        'automatic' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'manual' => 'Manual',
                        'automatic' => 'Automatic',
                    ]),
                Tables\Filters\Filter::make('visible')
                    ->query(fn(Builder $query): Builder => $query->where('is_visible', true)),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
