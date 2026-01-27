<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->withoutGlobalScopes();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(__('messages.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('sku')
                ->label(__('messages.sku'))
                ->required()
                ->maxLength(100),
            TextInput::make('price')
                ->label(__('messages.price'))
                ->numeric(),
            TextInput::make('stock_quantity')
                ->label(__('admin.products.stock_quantity'))
                ->numeric()
                ->integer()
                ->default(0),
            TextInput::make('reserved_quantity')
                ->label(__('admin.products.reserved_quantity'))
                ->numeric()
                ->integer()
                ->default(0),
            Select::make('status')
                ->label(__('admin.products.status'))
                ->options([
                    'draft' => __('admin.products.status_draft'),
                    'pending' => __('admin.products.status_pending'),
                    'published' => __('admin.products.status_published'),
                    'archived' => __('admin.products.status_archived'),
                ])
                ->default('published'),
            Toggle::make('track_inventory')
                ->label(__('admin.products.track_stock'))
                ->default(true),
            Toggle::make('is_default')
                ->label(__('admin.products.is_default_variant'))
                ->default(false),
            Toggle::make('is_enabled')
                ->label(__('admin.products.is_enabled'))
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->label(__('admin.products.available_quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(__('admin.products.stock_quantity'))
                    ->numeric()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
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
}
