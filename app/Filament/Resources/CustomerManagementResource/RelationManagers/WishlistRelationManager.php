<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use App\Models\Wishlist;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class WishlistRelationManager extends RelationManager
{
    protected static string $relationship = 'wishlist';

    protected static ?string $title = 'Wishlist';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('wishlist_id')
                    ->label(__('customers.wishlist'))
                    ->relationship('wishlist', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500),
                        Forms\Components\Toggle::make('is_public')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('customers.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('wishlist.name')
            ->columns([
                Tables\Columns\TextColumn::make('wishlist.name')
                    ->label(__('customers.wishlist_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('wishlist.description')
                    ->label(__('customers.description'))
                    ->searchable()
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('wishlist.items_count')
                    ->label(__('customers.items_count'))
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('wishlist.is_public')
                    ->label(__('customers.is_public'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('wishlist.is_active')
                    ->label(__('customers.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('customers.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'danger',
                        $state >= 50 => 'warning',
                        $state >= 20 => 'info',
                        $state >= 10 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('wishlist.created_at')
                    ->label(__('customers.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('wishlist')
                    ->label(__('customers.wishlist'))
                    ->relationship('wishlist', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label(__('customers.is_public'))
                    ->boolean()
                    ->trueLabel(__('customers.public_only'))
                    ->falseLabel(__('customers.private_only'))
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('customers.is_active'))
                    ->boolean()
                    ->trueLabel(__('customers.active_only'))
                    ->falseLabel(__('customers.inactive_only'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                EditAction::make(),
                Tables\Actions\DetachAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
